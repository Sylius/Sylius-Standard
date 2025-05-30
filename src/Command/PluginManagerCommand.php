<?php

declare(strict_types=1);

namespace App\Command;

use RuntimeException;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
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
        #[AutowireIterator('app.plugin_installer')]
        private readonly iterable $installers,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
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

        // Load from template
        if ($template) {
        $io->title(sprintf('Loading template: %s', $template));
            $configFile = $this->projectDir . '/store-creator/' . $template . '/store-creator.json';
            if (!file_exists($configFile)) {
            $io->error('Template config not found: ' . $configFile);
                return Command::FAILURE;
            }
            try {
                $data = json_decode(file_get_contents($configFile), true, 512, JSON_THROW_ON_ERROR);
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
        // CLI-specified or manual mode
        $raw = $input->getOption('plugins') ?: [];
            foreach ($raw as $p) {
            [$name, $ver] = explode(':', $p, 2) + [1 => null];
                if (!$ver) {
                $io->error("Invalid plugin format, expected name:version, got '$p'");
                    return Command::FAILURE;
                }
                $plugins[$name] = $ver;
            }
            if (empty($plugins) && $mode === self::MODE_MANUAL) {
            $io->title('Select plugin to manage');
                $supported = $this->getSupportedPlugins();
                $installed = $this->getInstalledPlugins();
                $rows = [];
                foreach ($supported as $pkg => $ver) {
                $rows[] = [$pkg, $ver, in_array($pkg, $installed, true) ? '✅' : ''];
                }
                $io->table(['Plugin','Version','Installed'], $rows);
                $choice = $io->choice('Select plugin', array_keys($supported));
                $plugins[$choice] = $supported[$choice];
            }
            if (empty($plugins)) {
            $io->error('No plugins specified.');
                return Command::FAILURE;
            }
            // Composer config
            Process::fromShellCommandline('composer config extra.symfony.allow-contrib true')
                ->setTimeout(0)->run();
            Process::fromShellCommandline('composer config repositories.sylius composer https://sylius.repo.packagist.com/sylius/')
                ->setTimeout(0)->run();
        }

        // Require stage
        if ($stage === 'require') {
        $io->section('📦 Requiring plugins');
            foreach ($plugins as $pkg => $ver) {
            Process::fromShellCommandline("composer require $pkg:$ver --no-scripts --no-interaction")
                ->mustRun(fn($t,$b) => $output->write($b));
                Process::fromShellCommandline("composer require $pkg:dev-booster --no-scripts --no-interaction")
                    ->mustRun(fn($t,$b) => $output->write($b));
            }
            $io->section('🔄 Restarting in install mode');
            $cmd = [PHP_BINARY, 'bin/console', self::$defaultName, '--stage=install', '--mode=auto'];
            if ($template) {
            $cmd[] = '--template=' . $template;
            }
            foreach ($plugins as $pkg => $ver) {
            $cmd[] = "--plugins=$pkg:$ver";
            }
            $proc = new Process($cmd, $this->projectDir);
            $proc->setTty(Process::isTtySupported())
            ->setTimeout(0)
            ->run(fn($t,$b) => $output->write($b));
            return $proc->getExitCode();
        }

        // Install stage
        $io->section('🔧 Installing plugins');
        foreach (array_keys($plugins) as $plugin) {
        $installer = $this->findInstallerFor($plugin);
            $installer->install($io);
        }

        // Post steps
        $this->runCommonPostSteps($io);

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
        // 1) Install assets
        $io->title('1) Installing assets via internal command');
        $app = $this->getApplication();

        $assetsCmd = $app->find('assets:install');
        $assetsIn  = new ArrayInput([
            'command'         => 'assets:install',
            'target'          => 'public',
            '--symlink'       => true,
            '--relative'      => true,
            '--no-interaction'=> true,
            '-vvv'            => true,
        ]);
        $assetsOut = new BufferedOutput();
        $code = $assetsCmd->run($assetsIn, $assetsOut);
        $io->writeln($assetsOut->fetch());
        $io->success(sprintf('assets:install exit code: %d', $code));

        // 2) Build frontend
        $io->title('2) Building front assets');
        $build = new Process(['yarn', 'run', 'build:prod'], $this->projectDir);
        $build
            ->setTimeout(0)
            ->setIdleTimeout(0)
            ->run(fn($t,$b) => $io->write($b));
        $io->success('build exit code: ' . $build->getExitCode());

        // 3) Apply schema update internally
        $io->title('3) Updating database schema');
        $schemaCmd = $app->find('doctrine:schema:update');
        $schemaIn  = new ArrayInput([
            'command'          => 'doctrine:schema:update',
            '--force'          => true,
            '--complete'       => true,
            '--no-interaction' => true,
        ]);
        $schemaOut = new BufferedOutput();
        $exit      = $schemaCmd->run($schemaIn, $schemaOut);
        $io->writeln($schemaOut->fetch());
        if ($exit !== Command::SUCCESS) {
            throw new RuntimeException('Database schema update failed');
        }
        $io->success('Database schema updated');

        // 4) Fixtures and cache
        $io->title('4) Loading fixtures and clearing cache');
        $fixturesCmd = $app->find('sylius:fixtures:load');
        $fixturesIn  = new ArrayInput([
            'command'          => 'sylius:fixtures:load',
            '--no-interaction' => true,
        ]);
        $fixturesOut = new BufferedOutput();
        $fixturesCmd->run($fixturesIn, $fixturesOut);
        $io->writeln($fixturesOut->fetch());
        $io->success('Fixtures loaded');

        foreach (['cache:clear', 'cache:warmup'] as $cmdName) {
            $cmd   = $app->find($cmdName);
            $input = new ArrayInput(['command' => $cmdName]);
            $cmd->run($input, new BufferedOutput());
        }
        $io->success('Cache cleared and warmed up');
    }

}
