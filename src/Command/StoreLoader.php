<?php

declare(strict_types=1);

namespace App\Command;

use Throwable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
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
        // Zamieniamy standardowy handler błędów na własny, który będzie ignorował
        // wszystkie warningi zawierające "getConsole_ErrorListenerService.php"
        set_error_handler(function(int $errno, string $errstr) {
            if (str_contains($errstr, 'getConsole_ErrorListenerService.php')) {
                return true; // uznajemy ten warning za obsłużony
            }
            return false;   // dla pozostałych komunikatów PHP używa domyślnego handlera
        });

        $io        = new SymfonyStyle($input, $output);
        $storeName = (string)$input->getArgument('store');
        $configPath = sprintf('%s/store-creator/%s/store-creator.json', $this->projectDir, $storeName);

        if (!file_exists($configPath)) {
            $io->error(sprintf('Configuration file not found: %s', $configPath));
            return Command::FAILURE;
        }

        $io->title(sprintf('Creating store: %s', $storeName));

        // Wczytanie całego JSON-a
        try {
            $json = file_get_contents($configPath);
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            $io->error('Invalid JSON in store-creator.json: ' . $e->getMessage());
            return Command::FAILURE;
        }

        //
        // 1) INSTALACJA PLUGINÓW
        //
        $io->section('[Store Loader] PLUGINS');
        if (!empty($data['plugins'])) {
            try {
                $this->runConsoleCommand('sylius:dx:plugin-manager', ["--template={$storeName}"], $io);
                $io->success('Plugins installed successfully.');
            } catch (Throwable $e) {
                // tutaj możemy sprawdzić, czy to jest właśnie nasz znany błąd
                if (str_contains($e->getMessage(), 'getConsole_ErrorListenerService.php')) {
                    $io->warning('Warning during plugin‐manager (cache missing) was ignored.');
                } else {
                    $io->error('Plugin installation failed: ' . $e->getMessage());
                    return Command::FAILURE;
                }
            }
        } else {
            $io->text('No plugins to install.');
        }

        //
        // 2) ŁADOWANIE FIXTURES
        //
        $io->section('[Store Loader] FIXTURES');
        if (!empty($data['fixtures']['suite'])) {
            try {
                $this->runConsoleCommand('sylius:dx:fixture-loader', [$storeName], $io);
                $io->success('Fixtures loaded successfully.');
            } catch (Throwable $e) {
                if (str_contains($e->getMessage(), 'getConsole_ErrorListenerService.php')) {
                    $io->warning('Warning during fixture‐loader (cache missing) was ignored.');
                } else {
                    $io->error('Fixture loading failed: ' . $e->getMessage());
                    return Command::FAILURE;
                }
            }
        } else {
            $io->text('No fixtures suite specified.');
        }

        //
        // 3) ZAINSTALUJ Intervention Image (fabryka obrazków)
        //
        $io->section('[Store Loader] Add Intervention Image package');
        try {
            $process = Process::fromShellCommandline(
                'composer require intervention/image',
                $this->projectDir
            );
            $process->run(fn($type, $buffer) => $io->write($buffer));
            if ($process->getExitCode() !== 0) {
                throw new ProcessFailedException($process);
            }
            $io->success('intervention/image installed.');
        } catch (Throwable $e) {
            // nawet jeśli instalacja się nie powiedzie, kontynuujemy dalej
            $io->warning('Failed to install intervention/image (ignoring): ' . $e->getMessage());
        }

        //
        // 4) ZASTOSUJ THEME (SCSS + logo)
        //
        $io->section('[Store Loader] THEMES');
        if (!empty($data['themes'])) {
            try {
                $this->runConsoleCommand('sylius:dx:theme-loader', [$storeName], $io);
                $io->success('Theme applied successfully.');
            } catch (Throwable $e) {
                if (str_contains($e->getMessage(), 'getConsole_ErrorListenerService.php')) {
                    $io->warning('Warning during theme‐loader (cache missing) was ignored.');
                } else {
                    $io->error('Theme application failed: ' . $e->getMessage());
                    return Command::FAILURE;
                }
            }
        } else {
            $io->text('No themes to apply.');
        }

        $io->success('Store creation complete!');
        return Command::SUCCESS;
    }

    /**
     * Uruchamia dowolną komendę "bin/console X Y Z" jako nowy proces.
     * Jeżeli cokolwiek pójdzie nie tak (z wyjątkiem naszej reguły "getConsole_ErrorListenerService.php"),
     * wyrzuci wyjątek, który można przechwycić wyżej.
     */
    private function runConsoleCommand(
        string $command,
        array  $arguments,
        SymfonyStyle $io,
    ): void {
        $parts = array_merge(['bin/console', $command], $arguments);
        $process = Process::fromShellCommandline(
            implode(' ', $parts),
            $this->projectDir
        );

        $process
            ->setTty(Process::isTtySupported())
            ->setTimeout(0)
            ->mustRun(fn($type, $buffer) => $io->write($buffer))
        ;
        // jeżeli exit code != 0 → mustRun() rzuci ProcessFailedException
    }
}
