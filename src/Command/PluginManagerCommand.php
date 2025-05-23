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

    protected const MODE_MANUAL = 'manual';
    protected const MODE_AUTO = 'auto';

    protected static $defaultName = 'sylius:plugin-manager';

    public function __construct(#[AutowireIterator('app.plugin_installer')] private readonly iterable $installers)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('mode', null, InputOption::VALUE_OPTIONAL, 'manual|auto', self::MODE_MANUAL)
            ->addOption('stage', null, InputOption::VALUE_OPTIONAL, 'require|install', 'require')
            ->addOption('plugins', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Plugin names to process, e.g. sylius/return-plugin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Sylius Plugin Manager');

        $mode = $input->getOption('mode');
        $stage = $input->getOption('stage');
        $plugins = $input->getOption('plugins');

        $supportedPlugins = $this->getSupportedPlugins();
        $installedPlugins = $this->getInstalledPlugins();

        if ($mode === self::MODE_MANUAL) {
            $io->title('Select plugin to manage');
            $rows = [];
            foreach ($supportedPlugins as $plugin => $version) {
                $rows[] = [$plugin, $version, in_array($plugin, $installedPlugins, true) ? '✅' : ''];
            }
            $io->table(['Plugin', 'Version', 'Installed'], $rows);

            $selected = $io->choice('Select plugin to manage', array_keys($supportedPlugins));

            if (empty($selected)) {
                $io->warning('No plugins selected, aborting.');
                return Command::SUCCESS;
            }

            $plugins[$selected] = $supportedPlugins[$selected];
        }

        if (empty($plugins)) {
            $io->error('No plugins specified.');
            return Command::FAILURE;
        }

        if ($stage === 'require') {
            $io->section('📦 Requiring plugins');
            foreach ($plugins as $package => $version) {
                // Require tagged version to resolve symfony recipes correctly
                Process::fromShellCommandline("composer require $package:$version --no-scripts --no-interaction")
                    ->mustRun(fn($type, $buffer) => $output->write($buffer));

                // Once recipes exists - require dev-booster branch to has access custom plugin code
                Process::fromShellCommandline("composer require $package:dev-booster --no-scripts --no-interaction")
                    ->mustRun(fn($type, $buffer) => $output->write($buffer));
            }

            $cmd = array_merge(
                [PHP_BINARY, 'bin/console', self::$defaultName, '--stage=install', '--mode=auto'],
                array_map(fn(string $plugin) => "--plugins={$plugin}", array_keys($plugins))
            );

            $io->section('🔄 Restarting plugin-manager in install mode');
            $proc = new Process($cmd, getcwd());
            $proc->setTty(Process::isTtySupported());
            $proc->run(fn($type, $buffer) => $output->write($buffer));

            return $proc->getExitCode();
        }

        // ==== STAGE=install ====
        $io->section('⚙️  Installing plugins');
        foreach ($plugins as $plugin) {
            $installer = $this->findInstallerFor($plugin);
            $installer->install($io);
            $installer->finalize($io);
        }

        $this->runCommonSteps($io);

        $io->success('All plugins installed.');
        return Command::SUCCESS;
    }

    private function findInstallerFor(mixed $plugin)
    {
        foreach ($this->installers as $installer) {
            if ($installer->supports($plugin)) {
                return $installer;
            }
        }

        throw new \RuntimeException(sprintf('No installer found for package "%s"', $plugin));
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
