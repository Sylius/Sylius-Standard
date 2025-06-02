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

    public function __construct(KernelInterface $kernel)
    {
        parent::__construct();

        $this->projectDir = $kernel->getProjectDir();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('store', InputArgument::REQUIRED, 'Name of the store directory under store-creator/')
            ->addOption('skip-build', null, InputOption::VALUE_NONE, 'Skip running webpack encore build');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $storeName = $input->getArgument('store');
        $configPath = sprintf('%s/store-creator/%s/store-creator.json', $this->projectDir, $storeName);

        if (!file_exists($configPath)) {
            $io->error(sprintf('Configuration file not found: %s', $configPath));
            return Command::FAILURE;
        }

        $io->title(sprintf('Creating store: %s', $storeName));

        $json = file_get_contents($configPath);
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $io->section('[Store Loader] PLUGINS');
        if (!empty($data['plugins'])) {
            $process = $this->runConsoleCommand(
                'sylius:dx:plugin-manager',
                [sprintf('--template=%s', $storeName)],
                $io,
            );
            if ($process->getExitCode() !== 0) {
                $io->error('Plugin installation failed.');
                return Command::FAILURE;
            }
        }

        $io->section('[Store Loader] FIXTURES');
        if ($data['fixtures']['suite'] ?? false) {
            $io->section('Loading fixtures');
            $process = $this->runConsoleCommand(
                'sylius:dx:fixture-loader',
                [$storeName],
                $io,
            );
            if ($process->getExitCode() !== 0) {
                $io->error('Fixture loading failed.');
                return Command::FAILURE;
            }
        }

        $io->section('[Store Loader] Add Intervention Image package');
        $process = Process::fromShellCommandline(
            'composer require intervention/image',
            $this->projectDir,
        );
        $process->run(fn($type, $buffer) => $io->write($buffer));

        $io->section('[Store Loader] THEMES');
        if ($data['themes'] ?? false) {
            $io->section('Applying theme');
            $process = $this->runConsoleCommand(
                'sylius:dx:theme-loader',
                [$storeName],
                $io,
            );
            if ($process->getExitCode() !== 0) {
                $io->error('Theme application failed.');
                return Command::FAILURE;
            }
        }

        $io->success('Store creation complete!');
        return Command::SUCCESS;
    }

    private function runConsoleCommand(
        string $command,
        array $arguments,
        SymfonyStyle $io,
    ): Process {
        $parts = array_merge(["bin/console", $command], $arguments);
        $process = Process::fromShellCommandline(
            implode(' ', $parts),
            $this->projectDir
        );

        $process
            ->setTty(Process::isTtySupported())
            ->setTimeout(0)
            ->mustRun(fn ($type, $buffer) => $io->write($buffer))
        ;

        return $process;
    }
}
