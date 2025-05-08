<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class PluginWizardCommand extends Command
{
    protected static $defaultName = 'app:plugin:install';

    private const SUPPORTED_PLUGINS = [
        'marketplace-plugin',
    ];

    protected function configure(): void
    {
        $this
        ->setDescription('Interactive wizard to install and configure a Sylius plugin')
        ->addArgument('plugin', InputArgument::OPTIONAL, 'The plugin name to install');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // 1) Uncommitted changes
        $this->checkUncommittedChanges($io);

        // 2) Plugin selection
        $plugin = $input->getArgument('plugin') ?: $io->choice(
        'Which plugin do you want to install?',
        self::SUPPORTED_PLUGINS
    );

        // 3) Validate plugin
        if (!in_array($plugin, self::SUPPORTED_PLUGINS, true)) {
        $io->error("Plugin '$plugin' is not supported.");
            return Command::FAILURE;
        }

        // 4) Sylius Packagist token
        $token = trim((string) shell_exec("composer config --global --auth http-basic.sylius.repo.packagist.com.password 2>/dev/null"));
        if (!$token) {
        $token = $io->askHidden('Enter your Sylius Packagist token');
            shell_exec("composer config --global http-basic.sylius.repo.packagist.com token $token");
        }
        $io->section('Validating token...');
        if (!(new Process(['curl','-sf','-u',"token:$token",'https://sylius.repo.packagist.com/sylius/packages.json']))->run() === 0) {
            $io->error('Invalid token. Aborting.');
            return Command::FAILURE;
        }

        // 5) Composer require
        $io->section("Installing plugin '$plugin'...");
        $this->runProcess(['composer','require',$plugin,'--no-scripts','--no-interaction'], $io);

        // 6) Rector
        $io->section('Running Rector for code cleanup...');
        $this->runProcess(['vendor/bin/rector','process','src','--no-progress-bar','--no-diffs'], $io);

        // 7) Symfony cache warmup
        $io->section('Warming up Symfony cache...');
        $this->runProcess(['bin/console','cache:warmup'], $io);

        // 8) Doctrine migrations
        $io->section('Running migrations...');
        $this->runProcess(['bin/console','doctrine:migrations:migrate','--no-interaction'], $io);

        // 9) Copy required templates
        $io->section('Copying required Sylius templates...');
        $this->copyTemplates($io, 'vendor/sylius/plus-marketplace-suite-plugin/templates', 'templates', [
        'bundles/SyliusAdminBundle/Order/Show/Summary/_totals.html.twig',
        'bundles/SyliusAdminBundle/Product/Show/_header.html.twig',
        // ... add rest
    ]);

        // 10) Optional templates
        if ($io->confirm('Copy optional marketplace templates?')) {
        $io->section('Copying optional templates...');
            $this->copyTemplates($io, 'vendor/sylius/plus-marketplace-suite-plugin/templates', 'templates', [
            'bundles/SyliusAdminBundle/Layout/_logo.html.twig',
            // ... rest
        ]);
        }

        // 11) Final cache warmup & assets
        $io->section('Final cache warmup & installing assets...');
        $this->runProcess(['bin/console','cache:warmup'], $io);
        $this->runProcess(['bin/console','assets:install'], $io);

        // 12) Encore build
        $choice = $io->choice('Run yarn encore?', ['dev','production','skip'], 'skip');
        if ($choice !== 'skip') {
        $io->section("Running 'yarn encore $choice'...");
            $this->runProcess(['yarn','encore',$choice], $io);
        }

        $io->success("Plugin '$plugin' installed successfully.");
        return Command::SUCCESS;
    }

    private function checkUncommittedChanges(SymfonyStyle $io): void
    {
        $status = trim((string) shell_exec('git status --porcelain'));
        if ($status) {
        $io->warning('Uncommitted changes detected!');
            if (!$io->confirm('Proceed anyway?')) {
            $io->error('Aborted by user.');
                exit(Command::FAILURE);
            }
        }
    }

    private function runProcess(array $cmd, SymfonyStyle $io): void
    {
        $process = new Process($cmd);
        $process->setTty(Process::isTtySupported());
        $process->run();

        if (!$process->isSuccessful()) {
            $io->error($process->getErrorOutput());
            exit(Command::FAILURE);
        }
}

private function copyTemplates(SymfonyStyle $io, string $sourceBase, string $destBase, array $files): void
    {
        $fs = new Filesystem();
        foreach ($files as $path) {
        $src = rtrim($sourceBase, '/').'/'.$path;
            $dest = rtrim($destBase, '/').'/'.$path;
            try {
                $fs->mkdir(dirname($dest));
                $fs->copy($src, $dest, true);
                $io->text("Copied $path");
            } catch (IOExceptionInterface $e) {
                $io->error("Failed to copy $path: ".$e->getMessage());
                exit(Command::FAILURE);
            }
        }
    }
}
