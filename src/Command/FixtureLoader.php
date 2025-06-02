<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'sylius:dx:fixture-loader',
    description: 'Load fixtures from configuration'
)]
class FixtureLoader extends Command
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ){
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('store', InputOption::VALUE_REQUIRED, 'Name of the store directory under store-creator/');
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite existing theme files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $storeName = $input->getArgument('store');
        $configPath = sprintf('%s/store-creator/%s/store-creator.json', $this->projectDir, $storeName);
        $fixturesPath = sprintf('%s/store-creator/%s/fixtures/fixtures.yaml', $this->projectDir, $storeName);

        if (!file_exists($configPath)) {
            $io->error(sprintf('Configuration file not found: %s', $configPath));
            return Command::FAILURE;
        }

        $io->title(sprintf('Loading fixtures for store: %s', $storeName));

        $json = file_get_contents($configPath);
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $result = copy($fixturesPath, $this->projectDir . '/config/packages/fixtures.yaml');
        if (!$result) {
            $io->error(sprintf('Failed to copy fixtures from %s to %s', $fixturesPath, $this->projectDir . '/config/packages/fixtures.yaml'));
            return Command::FAILURE;
        }

        // Skopiuj wszystkie zdjęcia
        $imagesDir = sprintf('%s/store-creator/%s/fixtures/images', $this->projectDir, $storeName);
        if (is_dir($imagesDir)) {
            $io->section('Copying images');
            $destinationDir = $this->projectDir . '/var/fixture_img';
            $process = Process::fromShellCommandline(
                sprintf('cp -r %s/* %s', escapeshellarg($imagesDir), escapeshellarg($destinationDir)),
            );
            $process->run(
                function ($type, $buffer) use ($io) {
                    $io->write($buffer);
                }
            );
            if ($process->getExitCode() !== 0) {
                $io->error('Failed to copy images.');
                return Command::FAILURE;
            }
        } else {
            $io->warning('No images directory found, skipping image copy.');
        }

        if (!empty($data['fixtures']['suite'] ?? null)) {
            $suite = $data['fixtures']['suite'];
            $io->section(sprintf('Loading fixtures suite: %s', $suite));
            $process = $this->runConsoleCommand('sylius:fixtures:load', [$suite, '--no-interaction'], $io);
            if ($process->getExitCode() !== 0) {
                $io->error('Fixtures loading failed.');
                return Command::FAILURE;
            }
        }

        $io->success('Fixtures loaded successfully.');

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
