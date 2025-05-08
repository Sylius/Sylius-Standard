<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'project:install-plugins',
    description: 'Installs and configures Sylius plugins based on a JSON config file'
)]
class ProjectWizardCommand extends Command
{
    protected function configure(): void
    {
        $this
        ->setDescription('Installs and configures Sylius plugins based on a JSON config file')
        ->addArgument(
            'config-file',
            InputArgument::OPTIONAL,
            'Path to booster JSON config',
            'booster.json'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $configPath = $input->getArgument('config-file');

        // 1) Load and parse JSON config
        if (!file_exists($configPath)) {
        $io->error("Configuration file '$configPath' not found.");
            return Command::FAILURE;
        }
        $raw = file_get_contents($configPath);
        $data = json_decode($raw, true);
        if (!is_array($data) || !isset($data['plugins']) || !is_array($data['plugins'])) {
        $io->error('Invalid config format: expected top-level "plugins" object.');
            return Command::FAILURE;
        }

        // 2) Extract plugins list
        $plugins = $data['plugins']; // ['vendor/pkg' => '^1.0', ...]
        $io->title('Plugins to install:');
        foreach ($plugins as $pkg => $version) {
        $io->text(" - $pkg ($version)");
        }
        $io->newLine();

        // 3) Composer require each plugin with version
        foreach ($plugins as $pkg => $version) {
        $io->section("Installing $pkg:");
            $requireArg = sprintf('%s:%s', $pkg, $version);
            $proc = new Process(['composer', 'require', $requireArg, '--no-interaction']);
            $proc->setTty(Process::isTtySupported());
            $proc->run();
            if (!$proc->isSuccessful()) {
            $io->error("Failed to install $pkg:\n" . $proc->getErrorOutput());
                return Command::FAILURE;
            }
        }

        // 4) Optional post-install steps
        if (isset($plugins['sylius/cms-plugin'])) {
        $io->section('Running CMS post-install steps');
            Process::fromShellCommandline('composer config extra.symfony.allow-contrib true')->run();
            Process::fromShellCommandline('yarn add trix@^2.0.0 swiper@^11.2.6')->run();
        }
        if (isset($plugins['loyalty']) || isset($plugins['sylius/loyalty-plugin'])) {
        $io->section('Applying Loyalty rector set');
            Process::fromShellCommandline('vendor/bin/rector')->run();
        }

        // 5) Common final steps
        $io->section('Running database migrations');
        Process::fromShellCommandline('bin/console doctrine:migrations:migrate --no-interaction')->run();

        $io->section('Clearing cache');
        Process::fromShellCommandline('bin/console cache:clear')->run();

        $io->success('All plugins installed and configured.');
        return Command::SUCCESS;
    }
}
