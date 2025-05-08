<?php
// src/Command/PluginWizardCommand.php

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
        ->addArgument('plugin', InputArgument::OPTIONAL, 'The plugin alias or package name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // 1) Check for uncommitted changes
        $this->checkUncommittedChanges($io);

        // 2) Ask for plugin if not provided
        $plugin = $input->getArgument('plugin')
        ?: $io->choice('Which plugin do you want to install?', self::SUPPORTED_PLUGINS);

        if (!in_array($plugin, self::SUPPORTED_PLUGINS, true)) {
        $io->error("Plugin '$plugin' is not supported.");
            return Command::FAILURE;
        }

        // 3) Ensure composer uses Sylius private repo
        $this->configureRepository($io);

        // 4) Attempt installation
        $io->section("Installing plugin '$plugin'...");
        $install = new Process(['composer', 'require', $plugin, '--no-scripts', '--no-interaction']);
        $install->setTty(Process::isTtySupported());
        $install->run();

        if (!$install->isSuccessful()) {
        $io->error("Failed to install plugin '$plugin':");
            $io->text($install->getErrorOutput());

            // 5) Show available versions
            $io->section("Available versions for '$plugin':");
            $show = new Process(['composer', 'show', $plugin, '--all', '--available']);
            $show->run();
            if ($show->isSuccessful()) {
            $io->text($show->getOutput());
            } else {
            $io->warning('Could not list versions: ' . $show->getErrorOutput());
            }

            // 6) Dry-run update with verbose output
            $io->section('Attempting dry-run update for more details...');
            $dry = new Process(['composer', 'update', $plugin, '--dry-run', '-vvv']);
            $dry->run();
            $io->text($dry->getErrorOutput() ?: $dry->getOutput());

            return Command::FAILURE;
        }

        $io->success("Plugin '$plugin' installed successfully.");

        // 7) Post-install steps
        $steps = [
        ['label' => 'Code cleanup (Rector)', 'cmd' => ['vendor/bin/rector', 'process', 'src', '--no-progress-bar', '--no-diffs']],
        ['label' => 'Warming up cache', 'cmd' => ['bin/console', 'cache:warmup']],
        ['label' => 'Running migrations', 'cmd' => ['bin/console', 'doctrine:migrations:migrate', '--no-interaction']],
        ['label' => 'Installing assets', 'cmd' => ['bin/console', 'assets:install']],
    ];
        foreach ($steps as $step) {
        $io->section($step['label']);
            $this->runProcess($step['cmd'], $io);
        }

        // 8) Copy templates
        $io->section('Copying required templates...');
        $this->copyTemplates($io, 'vendor/sylius/plus-marketplace-suite-plugin/templates', 'templates', [
        // required list
    ]);
        if ($io->confirm('Copy optional marketplace templates?')) {
        $io->section('Copying optional templates...');
            $this->copyTemplates($io, 'vendor/sylius/plus-marketplace-suite-plugin/templates', 'templates', [
            // optional list
        ]);
        }

        // 9) Encore build
        if ($io->confirm('Run frontend build (yarn encore)?')) {
        $choice = $io->choice('Choose build mode', ['dev', 'production'], 'dev');
            $io->section("Running 'yarn encore $choice'");
            $this->runProcess(['yarn', 'encore', $choice], $io);
        }

        $io->success('All done!');
        return Command::SUCCESS;
    }

    private function configureRepository(SymfonyStyle $io): void
    {
        $io->section('Configuring Sylius Packagist repository...');
        Process::fromShellCommandline('composer config repositories.sylius composer https://sylius.repo.packagist.com/sylius/')
            ->run();
        $io->success('Repository configured.');
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

private function copyTemplates(SymfonyStyle $io, string $srcBase, string $destBase, array $files): void
    {
        $fs = new Filesystem();
        foreach ($files as $rel) {
        $src = rtrim($srcBase, '/') . '/' . $rel;
            $dest = rtrim($destBase, '/') . '/' . $rel;
            try {
                $fs->mkdir(dirname($dest));
                $fs->copy($src, $dest, true);
                $io->text("Copied: $rel");
            } catch (IOExceptionInterface $e) {
                $io->error("Error copying $rel: " . $e->getMessage());
                exit(Command::FAILURE);
            }
        }
    }
}
