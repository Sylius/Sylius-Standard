<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class ProjectWizardCommand extends Command
{
    protected static $defaultName = 'project:install-plugins';
    protected function configure(): void
    {
        $this
            ->setDescription('Installs and configures Sylius plugins in one go')
            ->addArgument('plugins', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'List of plugin package names');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $plugins = $input->getArgument('plugins');

        // 1) Composer require each plugin
        foreach ($plugins as $pkg) {
            $io->section("Installing {$pkg}");
            $proc = new Process(['composer', 'require', $pkg, '--no-interaction']);
            $proc->setTty(Process::isTtySupported())->run();
            if (!$proc->isSuccessful()) {
                $io->error("Błąd instalacji {$pkg}:\n" . $proc->getErrorOutput());
                return Command::FAILURE;
            }
        }

        // 2) Special steps per plugin
        if (in_array('sylius/cms-plugin', $plugins, true)) {
            $io->section('Running CMS post-install steps');
            // e.g. allow-contrib, yarn add trix, etc.
            Process::fromShellCommandline('composer config extra.symfony.allow-contrib true')->run();
            Process::fromShellCommandline('yarn add trix@^2.0.0 swiper@^11.2.6')->run();
        }
        if (in_array('loyalty', $plugins, true) || in_array('sylius/loyalty-plugin', $plugins, true)) {
            $io->section('Applying Loyalty rector set');
            // edycja rector.php itd.
            // ...
            Process::fromShellCommandline('vendor/bin/rector')->run();
        }

        // 3) Common final steps
        $io->section('Running database migrations');
        Process::fromShellCommandline('bin/console doctrine:migrations:migrate --no-interaction')->run();

        $io->section('Clearing cache');
        Process::fromShellCommandline('bin/console cache:clear')->run();

        $io->success('All plugins installed and configured.');
        return Command::SUCCESS;
    }
}
