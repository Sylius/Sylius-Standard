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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'project:install-plugins',
    description: 'Installs and configures Sylius plugins based on a JSON config file'
)]
class ProjectInstallPluginsCommand extends Command
{
    protected function configure(): void
    {
        $this
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

        if (!file_exists($configPath)) {
            $io->error("Configuration file '$configPath' not found.");
            return Command::FAILURE;
        }

        $data = json_decode(file_get_contents($configPath), true);
        if (!isset($data['plugins']) || !is_array($data['plugins'])) {
            $io->error('Invalid config: missing "plugins" array.');
            return Command::FAILURE;
        }

        $io->title('Plugins to install:');
        foreach ($data['plugins'] as $pkg => $version) {
            $io->text(" - $pkg ($version)");
        }
        $io->newLine();

        $io->section('Configuring Symfony Flex to auto-accept contrib recipes');
        Process::fromShellCommandline('composer config extra.symfony.allow-contrib true')->run();

        if (count($data['plugins']) === 0) {
            $io->success('No plugins to install.');
            return Command::SUCCESS;
        }

        $io->section('Add Sylius Packagist repository');
        Process::fromShellCommandline('composer config repositories.sylius composer https://sylius.repo.packagist.com/sylius/')->run();

        foreach ($data['plugins'] as $pkg => $version) {
            $io->section("Installing $pkg");
            $args = ['composer', 'require', sprintf('%s:%s', $pkg, $version), '--no-interaction', '--no-scripts'];
            if (in_array($pkg, ['sylius/multi-source-inventory-plugin', 'sylius/loyalty-plugin', 'sylius/return-plugin'], true)) {
                $args[] = '--no-scripts';
            }
            $proc = new Process($args);
            $proc->setTty(Process::isTtySupported());
            $proc->run();
            if (!$proc->isSuccessful()) {
                $io->error("Failed to install $pkg:\n" . $proc->getErrorOutput());
                return Command::FAILURE;
            }
        }

        // Obejście braku receptur w bitbag elasticsearch
        if ($pkg === 'sylius/b2b-kit') {
            // 1. Import required config into config/packages/_sylius.yaml
            Process::fromShellCommandline(
                'sed -i "/imports:/a \    - { resource: \'@BitBagSyliusElasticsearchPlugin/config/config.yml\' }" config/packages/_sylius.yaml'
            )->run();

            // 2. Import routing before sylius_shop in config/routes.yaml
            Process::fromShellCommandline(
                'sed -i "/sylius_shop:/i \bitbag_sylius_elasticsearch_plugin:\n    resource: \'@BitBagSyliusElasticsearchPlugin/config/routing.yml\'" config/routes.yaml'
            )->run();

            // 3. Remove the Elasticsearch plugin routing from config/routes.yaml
            Process::fromShellCommandline(
                'sed -i "/bitbag_sylius_elasticsearch_plugin:/,+1d" config/routes.yaml'
            )->run();
        }

        $io->section('Uruchamiam drugi przebieg post-install');
        $php = PHP_BINARY;
        $console = $this->getApplication()->getName() === 'console' ? 'bin/console' : $_SERVER['argv'][0];
        $process = new Process([$php, $console, 'project:configure-plugins', '--no-interaction']);
        $process->setTty(Process::isTtySupported());
        $process->run();
        if (!$process->isSuccessful()) {
            $io->error('Nie udało się wykonać post-install: ' . $process->getErrorOutput());
            return Command::FAILURE;
        }
        $io->section('Proces zależny śmignął, lecimy dalej');

        $io->section('Running database sync');
        $sync = Process::fromShellCommandline('bin/console doctrine:schema:update --force --complete');
        $sync->run();
        if (!$sync->isSuccessful()) {
            $io->error('Database sync failed: ' . $sync->getErrorOutput());
            return Command::FAILURE;
        }

        $io->section('Installing assets and building front');
        Process::fromShellCommandline('bin/console assets:install')->run();
        Process::fromShellCommandline('yarn encore dev')->run();

        $io->success('All plugins installed and configured successfully.');

        $io->section('Loading default fixtures');
        $fixtures = Process::fromShellCommandline('bin/console sylius:fixtures:load --no-interaction');
        $fixtures->setTty(Process::isTtySupported());
        $fixtures->run();
        if (!$fixtures->isSuccessful()) {
            $io->error('Fixtures load failed: ' . $fixtures->getErrorOutput());
            return Command::FAILURE;
        }

        $io->section('Running cache warmup in a fresh process');
        $warmup = new Process(['bin/console', 'cache:warmup'], getcwd());
        $warmup->run();
        if (!$warmup->isSuccessful()) {
            $io->warning('Cache warmup failed: ' . $warmup->getErrorOutput());
        }

        $this->getApplication()->getKernel()->shutdown();
        $io->success('All plugins installed and configured successfully.');

        return Command::SUCCESS;
    }
}
