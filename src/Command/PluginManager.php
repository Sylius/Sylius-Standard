<?php

declare(strict_types=1);

namespace App\Command;

use Throwable;
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
    protected const MODE_AUTO   = 'auto';

    protected static $defaultName = 'sylius:dx:plugin-manager';

    public function __construct(
        #[AutowireIterator('app.plugin_installer')] private readonly iterable $installers,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('template', null, InputOption::VALUE_OPTIONAL, 'Load plugins from store-creator/{template}/store-creator.json')
            ->addOption('mode',     null, InputOption::VALUE_OPTIONAL, 'manual|auto', self::MODE_MANUAL)
            ->addOption('stage',    null, InputOption::VALUE_OPTIONAL, 'require|install', 'require')
            ->addOption('plugins',  null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Plugin names to process, e.g. sylius/return-plugin:2.0.x-dev');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io       = new SymfonyStyle($input, $output);
        $template = $input->getOption('template');
        $mode     = $template ? self::MODE_AUTO : $input->getOption('mode');
        $stage    = $input->getOption('stage');
        $plugins  = [];

        // 0) Najpierw wyczyść cache, żeby nie było konfliktu przy kolejnych reloadach kontenera
        $this->runConsole(['bin/console', 'cache:clear', '--no-debug'], $io);

        //
        // 1) Wczytywanie listy pluginów
        //
        if ($template) {
            $io->title(sprintf('Loading template: %s', $template));
            $configPath = sprintf('%s/store-creator/%s/store-creator.json', $this->projectDir, $template);
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

            // Zawsze ustawiamy Flex aby akceptował contrib i dodajemy Sylius Packagist repo
            Process::fromShellCommandline('composer config extra.symfony.allow-contrib true')->run();
            Process::fromShellCommandline('composer config repositories.sylius composer https://sylius.repo.packagist.com/sylius/')->run();
        }

        //
        // 2) Jeżeli etap = "require", to wykonujemy composer require ... i restartujemy siebie w trybie "install"
        //
        if ($stage === 'require') {
            $io->section('📦 Requiring plugins');
            foreach ($plugins as $package => $version) {
                Process::fromShellCommandline("composer require $package:$version --no-scripts --no-interaction")
                    ->mustRun(fn($type, $buffer) => $output->write($buffer));
                Process::fromShellCommandline("composer require $package:dev-booster --no-scripts --no-interaction")
                    ->mustRun(fn($type, $buffer) => $output->write($buffer));
            }

            $io->section('🔄 Restarting plugin-manager in install mode');
            $cmdParts = array_merge(
                ['bin/console', self::$defaultName, '--stage=install', '--mode=auto', '--no-debug'],
                $template ? ["--template={$template}"] : [],
                array_map(
                    fn($name, $ver) => "--plugins={$name}:{$ver}",
                    array_keys($plugins),
                    $plugins
                )
            );

            $this->runConsole($cmdParts, $io);
            return Command::SUCCESS;
        }

        //
        // 3) Etap "install" – uruchamiamy Rector, instalujemy pluginy i wykonujemy post‐steps
        //
        $io->section('🔧 Installing plugins');

        $io->title('Running Rector');
        $this->runConsole(
            ['vendor/bin/rector', 'process', 'src'],
            $io,
            ['cwd' => $this->projectDir]
        );

        $io->title('Installing plugins');
        foreach (array_keys($plugins) as $plugin) {
            $installer = $this->findInstallerFor($plugin);
            $installer->install($io);
        }

        try {
            $this->runCommonPostSteps($io);
        } catch (Throwable $e) {
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

        // assets:install
        $this->runConsole(['bin/console', 'assets:install', '-n', '--no-debug'], $io);

        // yarn encore production
        $this->runConsole(['yarn', 'encore', 'production'], $io, ['cwd' => $this->projectDir]);

        $io->section('Running database sync');
        $this->runConsole(['bin/console', 'doctrine:schema:update', '-n', '--force', '--complete', '--no-debug'], $io);

        $io->section('Loading default fixtures');
        $this->runConsole(['bin/console', 'sylius:fixtures:load', '-n', '--no-debug'], $io);

        // Cache clear & warmup
        $this->runConsole(['bin/console', 'cache:clear', '--no-debug'], $io);
        $this->runConsole(['bin/console', 'cache:warmup', '--no-debug'], $io);

        $io->success('All plugins installed and configured successfully.');
    }

    /**
     * Uruchamia pod‐proces Symfony Console w trybie „no-debug” i z zachowaniem tego samego var/cache/dev.
     *
     * @param string[]    $commandParts  Tablica fragmentów komendy np. ['bin/console','assets:install','-n','--no-debug']
     * @param SymfonyStyle $io
     * @param array<string,string> $options  Dodatkowe opcje (np. ['cwd' => '/pełna/ścieżka'])
     */
    private function runConsole(array $commandParts, SymfonyStyle $io, array $options = []): void
    {
        // Pobieramy ten sam katalog cache (np. var/cache/dev/ContainerXXX)
        $cacheDir = $this->getApplication()->getKernel()->getContainer()->getParameter('kernel.cache_dir');

        $env = [
            'SYMFONY_CACHE_DIR' => $cacheDir,
            'APP_ENV'           => 'dev',
        ];

        $process = new Process($commandParts, $options['cwd'] ?? $this->projectDir, $env);
        $process
            ->setTty(Process::isTtySupported())
            ->setTimeout(0)
            ->mustRun(function (string $type, string $buffer) use ($io) {
                $io->write($buffer);
            });
    }
}
