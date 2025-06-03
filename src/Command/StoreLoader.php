<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'sylius:dx:store-loader',
    description: 'Orchestrate Sylius installation: plugins, fixtures, themes',
)]
class StoreLoader extends Command
{
    private string $projectDir;

    private SymfonyStyle $io;

    public function __construct(KernelInterface $kernel)
    {
        parent::__construct();
        $this->projectDir = $kernel->getProjectDir();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('store', InputArgument::REQUIRED, 'Name of the store directory under store-creator/');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $storeName = (string)$input->getArgument('store');
        $configPath = sprintf('%s/store-creator/%s/store-creator.json', $this->projectDir, $storeName);

        if (!file_exists($configPath)) {
            $this->io->error(sprintf('Store configuration not found: %s', $configPath));
            return Command::FAILURE;
        }

        $this->io->title(sprintf('Creating store: %s', $storeName));

        $this->io->section('[Store Loader] PLUGINS');
        $this->runCommand(['bin/console', 'sylius:dx:plugin-manager', sprintf('--template=%s', $storeName), '--no-debug']);
        $this->runCommand(['bin/console', 'sylius:dx:plugin-manager', '--stage=install', sprintf('--template=%s', $storeName), '--no-debug']);

        $this->io->section('[Store Loader] FIXTURES');
        $this->runCommand(['bin/console', 'sylius:dx:fixture-loader', $storeName, '--no-debug']);

        $this->io->section('[Store Loader] THEMES');
        $this->runCommand(['composer require intervention/image', '--no-update']);
        $this->runCommand(['bin/console', 'sylius:dx:theme-loader', $storeName, '--no-debug']);

        $this->io->success('Store creation complete!');
        return Command::SUCCESS;
    }

    private function runCommand(array $command): int
    {
        $process = new Process($command, $this->projectDir);
        $process
            ->setTty(Process::isTtySupported())
            ->setTimeout(0)
            ->mustRun(fn(string $type, string $buffer) => $this->io->write($buffer));

        return $process->getExitCode();
    }
}
