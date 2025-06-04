<?php

declare(strict_types=1);

namespace App\Command\Fixture;

use App\Command\ConfigTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'sylius:dx:fixture:prepare',
    description: 'Prepare and load fixtures for a specific store',
)]
class FixturePrepare extends Command
{
    use ConfigTrait;

    public function __construct(
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ){
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $fixturesPath = $this->getFixturesPath();

        $io->section('[Fixture Loader] Preparing fixtures suite');

        $result = copy($fixturesPath, $this->projectDir . '/config/packages/fixtures.yaml');
        if (!$result) {
            $io->error(sprintf(
                'Failed to copy fixtures from %s to %s',
                $fixturesPath,
                $this->projectDir . '/config/packages/fixtures.yaml'
            ));
            return Command::FAILURE;
        }

        $imagesDir = sprintf('%s/store-preset/fixtures/images', $this->projectDir);
        if (is_dir($imagesDir)) {
            $io->section('Copying images');

            $destinationDir = $this->projectDir . '/var/fixture_img';

            // jeśli katalog docelowy nie istnieje, tworzymy go
            if (!is_dir($destinationDir)) {
                if (!mkdir($destinationDir, 0777, true) && !is_dir($destinationDir)) {
                    $io->error(sprintf('Failed to create directory %s', $destinationDir));
                    return Command::FAILURE;
                }
            }

            $process = Process::fromShellCommandline(sprintf(
                'cp -r %s/* %s',
                escapeshellarg($imagesDir),
                escapeshellarg($destinationDir)
            ));
            $process->run(function ($type, $buffer) use ($io) {
                $io->write($buffer);
            });

            if ($process->getExitCode() !== 0) {
                $io->error('Failed to copy images.');
                return Command::FAILURE;
            }
        } else {
            $io->warning('No images directory found, skipping image copy.');
        }

        $io->success('Fixtures prepared successfully.');
        return Command::SUCCESS;
    }
}
