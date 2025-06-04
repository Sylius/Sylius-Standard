<?php

declare(strict_types=1);

namespace App\Command;

use RuntimeException;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'sylius:dx:plugin-manager',
    description: 'Require and install Sylius plugins in one go'
)]
class PluginManager extends Command
{
    use ConfigTrait;

    protected const MODE_MANUAL = 'manual';
    protected const MODE_AUTO = 'auto';

    protected static $defaultName = 'sylius:dx:plugin-manager';

    private SymfonyStyle $io;

    public function __construct(
        #[AutowireIterator('app.plugin_installer')] private readonly iterable $installers,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('store', null, InputOption::VALUE_OPTIONAL, 'Load plugins from store-preset/store-preset.json')
            ->addOption('mode', null, InputOption::VALUE_OPTIONAL, 'manual|auto', self::MODE_MANUAL)
            ->addOption('stage', null, InputOption::VALUE_OPTIONAL, 'require|install', 'require')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $store = $input->getOption('store');
        $mode = $store ? self::MODE_AUTO : $input->getOption('mode');
        $stage = $input->getOption('stage');
        $plugins = [];

        if ($store) {
            $this->io->title(sprintf('Loading store: %s', $store));
            $configPath = sprintf('%s/store-preset/store-preset.json', $this->projectDir);
            if (!file_exists($configPath)) {
                $this->io->error(sprintf('Template config not found: %s', $configPath));
                return Command::FAILURE;
            }
            try {
                $data = json_decode((string)file_get_contents($configPath), true, 512, JSON_THROW_ON_ERROR);
            } catch (Exception $e) {
                $this->io->error('Invalid JSON in store config: ' . $e->getMessage());
                return Command::FAILURE;
            }
            $plugins = $data['plugins'] ?? [];
            if (empty($plugins)) {
                $this->io->warning('No plugins defined in store.');
                return Command::SUCCESS;
            }
        } else {
            $raw = $input->getOption('plugins') ?: [];
            foreach ($raw as $p) {
                [$name, $ver] = explode(':', $p, 2) + [1 => null];
                if (!$ver) {
                    $this->io->error("Invalid plugin format, expected name:version, got '$p'");
                    return Command::FAILURE;
                }
                $plugins[$name] = $ver;
            }

            if (empty($plugins) && $mode === self::MODE_MANUAL) {
                $this->io->title('Select plugin to manage');
                $supported = $this->getSupportedPlugins();
                $installed = $this->getInstalledPlugins();
                $rows = [];
                foreach ($supported as $pkg => $ver) {
                    $rows[] = [$pkg, $ver, in_array($pkg, $installed, true) ? '✅' : ''];
                }
                $this->io->table(['Plugin', 'Version', 'Installed'], $rows);
                $choice = $this->io->choice('Select plugin', array_keys($supported));
                $plugins[$choice] = $supported[$choice];
            }

            if (empty($plugins)) {
                $this->io->error('No plugins specified.');
                return Command::FAILURE;
            }

            Process::fromShellCommandline('composer config extra.symfony.allow-contrib true')->setTimeout(0)->run();
            Process::fromShellCommandline('composer config repositories.sylius composer https://sylius.repo.packagist.com/sylius/')->setTimeout(0)->run();
        }

        if ($stage === 'require') {
            $this->io->section('📦 Requiring plugins');
            foreach ($plugins as $package => $version) {
                Process::fromShellCommandline("composer require $package:$version --no-scripts --no-interaction")
                    ->setTimeout(0)
                    ->mustRun(fn($type, $buffer) => $output->write($buffer));
                Process::fromShellCommandline("composer require $package:dev-booster --no-scripts --no-interaction")
                    ->setTimeout(0)
                    ->mustRun(fn($type, $buffer) => $output->write($buffer));
            }

            return Command::SUCCESS;
        }

        $this->io->title('Running Rector');
        $this->runCommand(['vendor/bin/rector', 'process', 'src']);

        $this->io->title('Installing plugins');
        foreach (array_keys($plugins) as $plugin) {
            $installer = $this->findInstallerFor($plugin);
            $installer->install($this->io);
        }

        $this->runCommonPostSteps();
        $this->io->success('All plugins processed successfully.');

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

    private function runCommonPostSteps(): void
    {
        $this->io->title('Installing assets and building front');
        $this->runCommand(['bin/console', 'assets:install', '-n', '--no-debug']);
        $this->runCommand(['yarn', 'encore', 'production']);

        $this->io->section('Running database sync');
        $this->runCommand(['bin/console', 'doctrine:schema:update', '-n', '--force', '--complete', '--no-debug']);

//        $this->io->section('Loading default fixtures');
//        $this->runCommand(['bin/console', 'sylius:fixtures:load', '-n', '--no-debug'], $this->io);

        $this->io->success('All plugins installed and configured successfully.');
    }

    private function runCommand(array $command): int
    {
        $process = new Process($command, $this->projectDir);
        $process
            ->setTty(Process::isTtySupported())
            ->setTimeout(0)
            ->mustRun(fn(string $type, string $buffer) => $this->io->write($buffer));

        return $process->getExitCode();
    }
}
