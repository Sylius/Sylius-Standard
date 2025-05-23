<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'sylius:plugin-manager',
    description: 'Require and install Sylius plugins in one go'
)]
class PluginManagerCommand extends Command
{
    use PluginConfigTrait;

    protected static $defaultName = 'sylius:plugin-manager';

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
        if ($stage === 'require' && $input->getOption('no-interaction') === false) {
            // 1. Pobieramy deklarację wszystkich wspieranych pluginów
            $supportedPlugins = $this->getSupportedPlugins(); // [pkg => version, ...]
            $installedPlugins = $this->getInstalledPlugins($io);

            // 3. Wyświetlamy tabelę: Plugin | Version | Installed
            $io->title('Available Sylius plugins');
            $rows = [];
            foreach ($supportedPlugins as $pkg => $version) {
                $rows[] = [
                    $pkg,
                    $version,
                    isset($installedPlugins[$pkg]) ? '✅' : '',
                ];
            }
            $io->table(['Package', 'Version', 'Installed'], $rows);

            // 4. Wybór wielokrotny
            $choices = array_keys($supportedPlugins);
            // domyślnie zaznaczamy te już włączone
            $default = array_values(array_intersect($choices, array_keys($installedPlugins)));
            $selected = $io->choice(
                'Select plugin(s) to require',
                $choices,
                $default,
                true,
                true // multi-select
            );

            // 5. Jeżeli nic nie wybrano – wychodzimy
            if (empty($selected)) {
                $io->warning('No plugins selected, aborting.');
                return Command::SUCCESS;
            }

            // 6. Nadpisujemy listę $pkgs pluginami wybranymi przez użytkownika
            $packages = $selected;
        }


        if (empty($packages)) {
            $io->error('No plugins specified.');
            return Command::FAILURE;
        }

        if ($stage === 'require') {
            $io->section('📦 Requiring packages');
            foreach ($packages as $pkg) {
                Process::fromShellCommandline("composer require $pkg --no-scripts --no-interaction")
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
        // tu już $this->collectInstallers() znajdzie instalatory z vendor/,
        // możesz wykonać init/install/finalize dla każdego $pkgs.
        foreach ($packages as $pkg) {
            $installer = $this->findInstallerFor($pkg);
            $installer->init($io);      // optional
            $installer->install($io);
            $installer->finalize($io);
        }

        $io->success('All plugins installed.');
        return Command::SUCCESS;
    }
}
