<?php

declare(strict_types=1);

namespace App\Command;

use App\Plugin\Installer\PluginInstallerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(
    name: 'project:install-plugins',
    description: 'Installs and configures Sylius plugins based on a JSON config file'
)]
class ProjectWizardCommand extends Command
{
    /** @var iterable<PluginInstallerInterface> */
    private $installers;

    public function __construct(iterable $installers)
    {
        parent::__construct();
        $this->installers = $installers;
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'config-file',
                InputArgument::OPTIONAL,
                'Path to booster JSON config',
                'booster.json'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $configPath = $input->getArgument('config-file');

        if (!file_exists($configPath)) {
            $io->error("Configuration file '$configPath' not found.");
            return Command::FAILURE;
        }
        $data = json_decode(file_get_contents($configPath), true);
        if (!isset($data['plugins']) || !is_array($data['plugins'])) {
            $io->error('Invalid config: missing "plugins" array.');
            return Command::FAILURE;
        }

        $io->title('Plugins to install:');
        foreach ($data['plugins'] as $pkg => $version) {
            $io->text(" - $pkg ($version)");
        }
        $io->newLine();

        $io->section('Configuring Symfony Flex to auto-accept contrib recipes');
        Process::fromShellCommandline('composer config extra.symfony.allow-contrib true')->run();

        $filesystem = new Filesystem();

        if (count($data['plugins']) === 0) {
            $io->success('No plugins to install.');
            return Command::SUCCESS;
        }

        foreach ($data['plugins'] as $pkg => $version) {
            $io->section("Installing $pkg");
            $args = ['composer', 'require', sprintf('%s:%s', $pkg, $version), '--no-interaction'];
            if (in_array($pkg, ['sylius/multi-source-inventory-plugin', 'sylius/loyalty-plugin', 'sylius/return-plugin'], true)) {
                $args[] = '--no-scripts';
            }
            $proc = new Process($args);
            $proc->setTty(Process::isTtySupported());
            $proc->run();
            if (!$proc->isSuccessful()) {
                $io->error("Failed to install $pkg:\n" . $proc->getErrorOutput());
                return Command::FAILURE;
            }
        }

        foreach ($this->installers as $installer) {
            if ($installer->supports($pkg)) {
                $io->section('Running post-install steps for ' . $pkg);
                $installer->install($version);
                break;
            }
        }

//        if (isset($data['plugins']['sylius/cms-plugin'])) {
//            $io->section('Running CMS post-install steps');
//            Process::fromShellCommandline('yarn add trix@^2.0.0 swiper@^11.2.6')->run();
//        }

        // Multi Source Inventory Plugin
        if (isset($data['plugins']['sylius/multi-source-inventory-plugin'])) {
            $io->section('Recreating rector.php for Multi Source Inventory plugin');
            $rectorFile = getcwd() . '/rector.php';

            // Remove old file
            if (file_exists($rectorFile)) {
                $filesystem->remove($rectorFile);
                $io->text('Removed existing rector.php');
            }

            // Create new rector.php with required set
            $newContent = <<<'PHP'
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Sylius\SyliusRector\Set\SyliusPlus;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->importNames();
    $rectorConfig->removeUnusedImports();
    $rectorConfig->import(__DIR__ . '/vendor/sylius/sylius-rector/config/config.php');
    $rectorConfig->paths([
        __DIR__ . '/src'
    ]);
    $rectorConfig->sets([SyliusPlus::MULTI_SOURCE_INVENTORY_PLUGIN]);
};
PHP;

            $filesystem->dumpFile($rectorFile, $newContent);
            $io->text('Created new rector.php with MULTI_SOURCE_INVENTORY_PLUGIN set');

            // Run Rector
            $io->section('Running Rector for Multi Source Inventory');
            $rectorProc = new Process(['vendor/bin/rector']);
            $rectorProc->setTty(Process::isTtySupported());
            $rectorProc->run();
            if (!$rectorProc->isSuccessful()) {
                $io->error('Rector run failed: ' . $rectorProc->getErrorOutput());
                return Command::FAILURE;
            }
        }

        // Loyalty Plugin
        if (isset($data['plugins']['sylius/loyalty-plugin'])) {
            $io->section('Recreating rector.php for Loyalty plugin');
            $rectorFile = getcwd() . '/rector.php';

            // Remove old file
            if (file_exists($rectorFile)) {
                $filesystem->remove($rectorFile);
                $io->text('Removed existing rector.php');
            }
        }


        // Return Plugin
        if (isset($data['plugins']['sylius/return-plugin'])) {
            $io->section('Updating Return Plugin YAML configuration');
            $yamlFile = getcwd() . '/config/packages/sylius_return_plugin.yaml';
            if (file_exists($yamlFile)) {
                $config = Yaml::parseFile($yamlFile);
                // Override pdf_generator enabled
                $config['sylius_return']['pdf_generator']['enabled'] = false;
                // Add refund pdf_generator
                $config['sylius_refund']['pdf_generator']['enabled'] = false;
                // Dump YAML preserving imports
                $imports = $config['imports'] ?? [];
                $body = $config;
                unset($body['imports']);
                $newYaml = Yaml::dump([ 'imports' => $imports ] + $body, 4, 2);
                $filesystem->dumpFile($yamlFile, $newYaml);
                $io->text('Overwritten sylius_return_plugin.yaml with pdf_generator disabled and refund config');
            }

            $io->section('Recreating rector.php for Return plugin');

            $rectorFile = getcwd() . '/rector.php';

            // Remove old file
            if (file_exists($rectorFile)) {
                $filesystem->remove($rectorFile);
                $io->text('Removed existing rector.php');
            }

            // Create new rector.php with required set
            $newContent = <<<'PHP'
<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Sylius\SyliusRector\Set\SyliusPlus;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->importNames();
    $rectorConfig->removeUnusedImports();
    $rectorConfig->import(__DIR__ . '/vendor/sylius/sylius-rector/config/config.php');
    $rectorConfig->paths([
        __DIR__ . '/src'
    ]);
    $rectorConfig->sets([SyliusPlus::RETURN_PLUGIN]);
};
PHP;

            $filesystem->dumpFile($rectorFile, $newContent);
            $io->text('Created new rector.php with RETURN_PLUGIN set');

            // Run Rector
            $io->section('Running Rector for Return');
            $rectorProc = new Process(['vendor/bin/rector']);
            $rectorProc->setTty(Process::isTtySupported());
            $rectorProc->run();
            if (!$rectorProc->isSuccessful()) {
                $io->error('Rector run failed: ' . $rectorProc->getErrorOutput());
                return Command::FAILURE;
            }
        }

        $io->section('Running database sync');
        $sync = Process::fromShellCommandline('bin/console doctrine:schema:update --force --complete');
        $sync->run();
        if (!$sync->isSuccessful()) {
            $io->error('Database sync failed: ' . $sync->getErrorOutput());
            return Command::FAILURE;
        }

        $io->section('Installing assets and building front');
        Process::fromShellCommandline('bin/console assets:install')->run();
        Process::fromShellCommandline('yarn encore dev')->run();

        $io->success('All plugins installed and configured successfully.');

        $io->section('Loading default fixtures');
        $fixtures = Process::fromShellCommandline('bin/console sylius:fixtures:load --no-interaction');
        $fixtures->setTty(Process::isTtySupported());
        $fixtures->run();
        if (!$fixtures->isSuccessful()) {
            $io->error('Fixtures load failed: ' . $fixtures->getErrorOutput());
            return Command::FAILURE;
        }

        $io->section('Removing old cache directory');
        $filesystem->remove(getcwd().'/var/cache/dev');
        $io->text('Cache directory removed.');

        $io->section('Running cache warmup in a fresh process');
        $warmup = new Process(['bin/console', 'cache:warmup'], getcwd());
        $warmup->run();
        if (!$warmup->isSuccessful()) {
            $io->warning('Cache warmup failed: ' . $warmup->getErrorOutput());
        }

        $this->getApplication()->getKernel()->shutdown();
        $io->success('All plugins installed and configured successfully.');

        return Command::SUCCESS;
    }
}
