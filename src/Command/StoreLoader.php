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

        // 0) Zawsze najpierw wyczyść cache, żeby późniejsze podprocesy korzystały z aktualnej wersji kontenera
        $this->runConsole(['bin/console', 'cache:clear', '--no-debug'], $io);

        $storeName  = (string)$input->getArgument('store');
        $configPath = sprintf('%s/store-creator/%s/store-creator.json', $this->projectDir, $storeName);

        if (!file_exists($configPath)) {
            $io->error(sprintf('Configuration file not found: %s', $configPath));
            return Command::FAILURE;
        }

        $io->title(sprintf('Creating store: %s', $storeName));

        $json = file_get_contents($configPath);
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        //
        // 1) INSTALACJA PLUGINÓW
        //
        $io->section('[Store Loader] PLUGINS');
        if (!empty($data['plugins'])) {
            // uruchamiamy nasz helper, który dba o wspólny cache-dir i --no-debug
            $exitCode = $this->runConsole(
                ['bin/console', 'sylius:dx:plugin-manager', sprintf('--template=%s', $storeName), '--no-debug'],
                $io
            );
            if ($exitCode !== 0) {
                $io->error('Plugin installation failed.');
                return Command::FAILURE;
            }
            $io->success('Plugins installed successfully.');
        } else {
            $io->text('No plugins to install.');
        }

        //
        // 2) ŁADOWANIE FIXTURES
        //
        $io->section('[Store Loader] FIXTURES');
        if (!empty($data['fixtures']['suite'])) {
            $io->section('Loading fixtures');
            $exitCode = $this->runConsole(
                ['bin/console', 'sylius:dx:fixture-loader', $storeName, '--no-debug'],
                $io
            );
            if ($exitCode !== 0) {
                $io->error('Fixture loading failed.');
                return Command::FAILURE;
            }
            $io->success('Fixtures loaded successfully.');
        } else {
            $io->text('No fixtures suite specified.');
        }

        //
        // 3) DODANIE INTERVENTION/IMAGE (opcjonalnie – ignorujemy błędy)
        //
        $io->section('[Store Loader] Add Intervention Image package');
        $process = Process::fromShellCommandline(
            'composer require intervention/image',
            $this->projectDir
        );
        $process->run(fn(string $type, string $buffer) => $io->write($buffer));
        if (0 === $process->getExitCode()) {
            $io->success('intervention/image installed.');
        } else {
            $io->warning('Failed to install intervention/image (ignoring).');
        }

        //
        // 4) ZASTOSUJ THEME (SCSS + logo)
        //
        $io->section('[Store Loader] THEMES');
        if (!empty($data['themes'])) {
            $io->section('Applying theme');
            $exitCode = $this->runConsole(
                ['bin/console', 'sylius:dx:theme-loader', $storeName, '--no-debug'],
                $io
            );
            if ($exitCode !== 0) {
                $io->error('Theme application failed.');
                return Command::FAILURE;
            }
            $io->success('Theme applied successfully.');
        } else {
            $io->text('No themes to apply.');
        }

        $io->success('Store creation complete!');
        return Command::SUCCESS;
    }

    /**
     * Uruchamia pod‐proces Symfony Console w trybie „no-debug” i z zachowaniem tego samego var/cache/dev.
     *
     * @param string[]    $commandParts  Tablica fragmentów komendy np. ['bin/console', 'cache:clear', '--no-debug']
     * @param SymfonyStyle $io
     * @return int                     Kod wyjścia podprocessu
     */
    private function runConsole(array $commandParts, SymfonyStyle $io): int
    {
        // Zanim stworzymy Process, ustalamy ścieżkę do aktualnego katalogu cache.
        // Dzięki temu każdy "php bin/console" użyje tego samego cache, a nie będzie próbował wygenerować nowego
        $cacheDir = $this->getApplication()->getKernel()->getContainer()->getParameter('kernel.cache_dir');

        $io->section('[Store Loader] ==========CACHE DIR==========');
        $io->writeln($cacheDir);
        $io->section('[Store Loader] ==========CACHE DIR==========');
        // Ustawiamy zmienne środowiskowe tak, aby Symfony korzystało z dokładnie tego cache‐dir:
        $env = [
            'SYMFONY_CACHE_DIR' => $cacheDir,
            'APP_DEBUG'        => '0',
            'APP_ENV'           => 'dev',
        ];

        $process = new Process($commandParts, $this->projectDir, $env);
        $process
            ->setTty(Process::isTtySupported())
            ->setTimeout(0)
            ->run(function (string $type, string $buffer) use ($io) {
                $io->write($buffer);
            });

        return $process->getExitCode();
    }
}
