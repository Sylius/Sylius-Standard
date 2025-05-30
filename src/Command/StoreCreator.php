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
    name: 'sylius:store-creator',
    description: 'Orchestrate Sylius installation: plugins, fixtures, themes',
)]
class StoreCreator extends Command
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

        if (!empty($data['plugins'])) {
            $io->section('Installing plugins');
            $process = $this->runConsoleCommand(
                'sylius:plugin-manager',
                [sprintf('--template=%s', $storeName)],
                $io,
            );
            if ($process->getExitCode() !== 0) {
                $io->error('Plugin installation failed.');
                return Command::FAILURE;
            }
        }

        // Fixtures
//        if (!empty($data['fixtures']['suite'] ?? null)) {
//            $suite = $data['fixtures']['suite'];
//            $io->section(sprintf('Loading fixtures suite: %s', $suite));
//            $process = $this->runConsoleCommand('sylius:fixtures:load', [$suite], $io);
//            if ($process->getExitCode() !== 0) {
//                $io->error('Fixtures loading failed.');
//                return Command::FAILURE;
//            }
//        }

        // Theme
//        if (!empty($data['themes'])) {
//            $io->section('Applying theme');
//            $process = $this->runConsoleCommand('sylius:theme-creator', [$storeName], $io, $input->getOption('skip-build'));
//            if ($process->getExitCode() !== 0) {
//                $io->error('Theme application failed.');
//                return Command::FAILURE;
//            }
//        }

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
// przekazujemy TTY tak, jakby to była Twoja konsola
        $process
            ->setTty(Process::isTtySupported())
            ->setTimeout(0)
            ->run(function ($type, $buffer) use ($io) {
                $io->write($buffer);
            });

        return $process;
    }
}
