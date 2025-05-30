<?php

declare(strict_types=1);

namespace App\Command;

use RuntimeException;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
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
    use ConfigTrait;

    protected const MODE_MANUAL = 'manual';
    protected const MODE_AUTO = 'auto';

    protected static $defaultName = 'sylius:plugin-manager';

    public function __construct(
        #[AutowireIterator('app.plugin_installer')] private readonly iterable $installers
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('template', null, InputOption::VALUE_OPTIONAL, 'Load plugins from store-creator/{template}/store-creator.json')
            ->addOption('mode', null, InputOption::VALUE_OPTIONAL, 'manual|auto', self::MODE_MANUAL)
            ->addOption('stage', null, InputOption::VALUE_OPTIONAL, 'require|install', 'require')
            ->addOption('plugins', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Plugin names to process, e.g. sylius/return-plugin:2.0.x-dev');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $template = $input->getOption('template');
        $mode = $template ? self::MODE_AUTO : $input->getOption('mode');
        $stage = $input->getOption('stage');
        $plugins = [];

        if ($template) {
            // Load plugins from template config
            $io->title(sprintf('Loading template: %s', $template));
            $projectDir = $this->getApplication()->getKernel()->getProjectDir();
            $configPath = sprintf('%s/store-creator/%s/store-creator.json', $projectDir, $template);
            if (!file_exists($configPath)) {
                $io->error(sprintf('Template config not found: %s', $configPath));
                return Command::FAILURE;
            }
            try {
                $data = json_decode((string)file_get_contents($configPath), true, 512, JSON_THROW_ON_ERROR);
            } catch (Exception $e) {
                $io->error('Invalid JSON in template config: ' . $e->getMessage());
                return Command::FAILURE;
            }
            $plugins = $data['plugins'] ?? [];
            if (empty($plugins)) {
                $io->warning('No plugins defined in template.');
                return Command::SUCCESS;
            }
        } else {
            $plugins = $input->getOption('plugins');
            if ($plugins !== null) {
                $names    = array_map(fn($p) => explode(':', $p, 2)[0], $plugins);
                $versions = array_map(fn($p) => explode(':', $p, 2)[1], $plugins);
                $plugins  = array_combine($names, $versions) ?: [];
            }
            $supportedPlugins = $this->getSupportedPlugins();
            $installedPlugins = $this->getInstalledPlugins();

            $io->info('Configuring Symfony Flex to auto-accept contrib recipes');
            Process::fromShellCommandline('composer config extra.symfony.allow-contrib true')->run();

            $io->info('Add Sylius Packagist repository');
            Process::fromShellCommandline('composer config repositories.sylius composer https://sylius.repo.packagist.com/sylius/')->run();

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

                $plugins = $plugins ?? [];
                $plugins[$selected] = $supportedPlugins[$selected];
            }

            if (empty($plugins)) {
                $io->error('No plugins specified.');
                return Command::FAILURE;
            }
        }

        // Configure composer
        $io->info('Configuring Symfony Flex and Sylius Packagist');
        Process::fromShellCommandline('composer config extra.symfony.allow-contrib true')->setTimeout(0)->run();
        Process::fromShellCommandline('composer config repositories.sylius composer https://sylius.repo.packagist.com/sylius/')->setTimeout(0)->run();

        // Stage: require
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

            // Rerun in install mode
            $io->section('🔄 Restarting plugin-manager in install mode');
            $cmd = [PHP_BINARY, 'bin/console', self::$defaultName, '--stage=install', '--mode=auto'];
            if ($template) {
                $cmd[] = "--template={$template}";
            }
            foreach ($plugins as $name => $ver) {
                $cmd[] = "--plugins={$name}:{$ver}";
            }
            $process = new Process($cmd, $projectDir ?? null);
            $process->setTty(Process::isTtySupported());
            $process->setTimeout(0)->run(fn($type, $buffer) => $output->write($buffer));
            return $process->getExitCode();
        }

        // Stage: install
        $io->section('🔧 Installing plugins');
        foreach (array_keys($plugins) as $plugin) {
            $installer = $this->findInstallerFor($plugin);
            $installer->install($io);
        }

        try {
            $this->runCommonPostSteps($io);
        } catch (\Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->success('All plugins processed successfully.');
        return Command::SUCCESS;
    }

    private function findInstallerFor(mixed $plugin)
    {
        foreach ($this->installers as $installer) {
            if ($installer->supports($plugin)) {
                return $installer;
            }
        }

        throw new RuntimeException(sprintf('No installer found for package "%s"', $plugin));
    }

    private function runCommonPostSteps(SymfonyStyle $io): void
    {
        $io->title('Installing assets and building front');
        Process::fromShellCommandline('bin/console assets:install')->run();
        Process::fromShellCommandline('yarn encore dev')->run();

        $io->section('Running database sync');
        $sync = Process::fromShellCommandline('bin/console doctrine:schema:update --force --complete');
        $sync->run();
        if (!$sync->isSuccessful()) {
            $io->error('Database sync failed: ' . $sync->getErrorOutput());
            throw new Exception('Database sync failed');
        }

        $io->section('Loading default fixtures');
        $process = Process::fromShellCommandline('bin/console sylius:fixtures:load --no-interaction');
        $process->setTty(Process::isTtySupported());
        $process->setTimeout(0)->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException('Fixtures load failed: ' . $process->getErrorOutput());
        }

        $io->success('Fixtures loaded successfully.');
        $clear = new Process(['bin/console', 'cache:clear'], getcwd());
        $clear->setTimeout(0)->run();
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
