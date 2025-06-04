<?php

declare(strict_types=1);

namespace App\Command\Fixture;

use App\Command\ConfigTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'sylius:dx:fixture:load',
    description: 'Load fixtures from configuration'
)]
class FixtureLoad extends Command
{
    use ConfigTrait;

    public function __construct(
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ){
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('store', InputOption::VALUE_OPTIONAL, 'Name of the store directory under store-preset/', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $store = $this->getStoreName();

        if (empty($store)) {
            $store = $this->getStoreName();
        }

        $io->section('[Fixture Loader] Loading fixtures suite');
        $process = $this->runConsoleCommand([$store, '--no-interaction'], $io);
        if ($process->getExitCode() !== 0) {
            $io->error('Fixtures loading failed.');
            return Command::FAILURE;
        }

        $io->success('Fixtures suite loaded successfully.');

        return Command::SUCCESS;
    }

    private function runConsoleCommand(
        array $arguments,
        SymfonyStyle $io,
    ): Process {
        $parts = array_merge(["bin/console", 'sylius:fixtures:load'], $arguments);
        $process = Process::fromShellCommandline(
            implode(' ', $parts),
            $this->projectDir
        );

        $process
            ->setTty(Process::isTtySupported())
            ->setTimeout(0)
            ->run(function ($type, $buffer) use ($io) {
                $io->write($buffer);
            });

        return $process;
    }
}
