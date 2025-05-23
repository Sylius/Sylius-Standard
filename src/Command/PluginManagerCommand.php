<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'sylius:plugin-manager',
    description: 'Require and install Sylius plugins in one go'
)]
class PluginManagerCommand extends Command
{
    use PluginConfigTrait;

    protected static $defaultName = 'sylius:plugin-manager';

    public function __construct(#[AutowireIterator('app.plugin_installer')] private readonly iterable $installers)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('stage', null, InputOption::VALUE_REQUIRED, 'require|install', 'require')
            ->addOption('plugins', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Plugin names to process, e.g. sylius/return-plugin')
            ->addOption('no-interaction', 'n', InputOption::VALUE_NONE, 'Non-interactive mode');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $stage = $input->getOption('stage');
        $packages = $input->getOption('plugins');

        // 1) wybór pluginów (tylko w stage=require && interactive)
        if ($stage === 'require' && !$input->getOption('no-interaction')) {
            // 1. Pobieramy deklarację wszystkich wspieranych pluginów
            $supportedPlugins = $this->getSupportedPlugins(); // [pkg => version, ...]

            // 2. Pobieramy listę faktycznie zainstalowanych w vendor/
            $installedPlugins = $this->getInstalledPlugins(); // ['sylius/return-plugin', ...]

            // 3. Wyświetlamy tabelę: Package | Version | Installed
            $io->title('Available Sylius plugins');
            $rows = [];
            foreach ($supportedPlugins as $pkg => $version) {
                $rows[] = [
                    $pkg,
                    $version,
                    in_array($pkg, $installedPlugins, true) ? '✅' : '',
                ];
            }
            $io->table(['Package', 'Version', 'Installed'], $rows);

            // 4. Wybór wielokrotny
            $choices = array_keys($supportedPlugins);
            // domyślnie zaznaczamy te, które już są w vendor/
            $default = array_values(array_intersect($choices, $installedPlugins));

            // Uwaga: 5 argumentów: question, choices, default, maxAttempts, multiselect
            $selected = $io->choice(
                'Select plugin(s) to require',
                $choices,
            );

            // 5. Jeśli nic nie wybrano — kończymy
            if (empty($selected)) {
                $io->warning('No plugins selected, aborting.');
                return Command::SUCCESS;
            }

            // 6. Nadpisujemy wejściowy array $packages
            $packages[$selected] = $supportedPlugins[$selected];
        }


        if (empty($packages)) {
            $io->error('No plugins specified.');
            return Command::FAILURE;
        }

        if ($stage === 'require') {
            $io->section('📦 Requiring packages');
            foreach ($packages as $package => $version) {
                // Require tagged version to resolve symfony recipes correctly
                Process::fromShellCommandline("composer require $package:$version --no-scripts --no-interaction")
                    ->mustRun(fn($type, $buffer) => $output->write($buffer));

                // Once recipes exists - require dev-booster branch to has access custom plugin code
                Process::fromShellCommandline("composer require $package:dev-booster --no-scripts --no-interaction")
                    ->mustRun(fn($type, $buffer) => $output->write($buffer));
            }

            // 2) Self‐reexec w trybie install
            $cmd = array_merge(
                [PHP_BINARY, 'bin/console', self::$defaultName, '--stage=install', '--no-interaction'],
                array_map(fn($p) => "--plugins=$p", $packages)
            );

            $io->section('🔄 Restarting plugin-manager in install mode');
            $proc = new Process($cmd, getcwd());
            $proc->setTty(Process::isTtySupported());
            $proc->run(fn($type, $buffer) => $output->write($buffer));

            return $proc->getExitCode();
        }

        // ==== STAGE=install ====
        $io->section('⚙️  Installing plugins');
        foreach ($packages as $pkg) {
            $installer = $this->findInstallerFor($pkg);
            $installer->install($io);
            $installer->finalize($io);
        }

        $this->runCommonSteps($io);

        $io->success('All plugins installed.');
        return Command::SUCCESS;
    }

    private function findInstallerFor(mixed $pkg)
    {
        foreach ($this->installers as $installer) {
            if ($installer->supports($pkg)) {
                return $installer;
            }
        }

        throw new \RuntimeException(sprintf('No installer found for package "%s"', $pkg));
    }

    private function runCommonSteps(SymfonyStyle $io): void
    {
        $clear = new Process(['bin/console', 'cache:clear'], getcwd());
        $clear->run();
        if (!$clear->isSuccessful()) {
            $io->warning('Cache clear failed: ' . $clear->getErrorOutput());
        }

        $warmup = new Process(['bin/console', 'cache:warmup'], getcwd());
        $warmup->run();
        if (!$warmup->isSuccessful()) {
            $io->warning('Cache warmup failed: ' . $warmup->getErrorOutput());
        }

        $io->success('All plugins installed and configured successfully.');
    }
}
