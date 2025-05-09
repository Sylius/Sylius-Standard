<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'project:install-plugins',
    description: 'Installs and configures Sylius plugins based on a JSON config file'
)]
class ProjectWizardCommand extends Command
{
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

        // Load config
        if (!file_exists($configPath)) {
            $io->error("Configuration file '$configPath' not found.");
            return Command::FAILURE;
        }
        $data = json_decode(file_get_contents($configPath), true);
        if (!isset($data['plugins']) || !is_array($data['plugins'])) {
            $io->error('Invalid config: missing "plugins" array.');
            return Command::FAILURE;
        }

        // Show plugins
        $io->title('Plugins to install:');
        foreach ($data['plugins'] as $pkg => $version) {
            $io->text(" - $pkg ($version)");
        }
        $io->newLine();

        // Ensure auto-accept Symfony recipes
        $io->section('Configuring Symfony Flex to auto-accept contrib recipes');
        Process::fromShellCommandline('composer config extra.symfony.allow-contrib true')->run();

        $filesystem = new Filesystem();

        // Install each plugin
        foreach ($data['plugins'] as $pkg => $version) {
            $io->section("Installing $pkg");
            $args = ['composer', 'require', sprintf('%s:%s', $pkg, $version), '--no-interaction'];
            // disable scripts for certain packages
            if (in_array($pkg, ['sylius/multi-source-inventory-plugin', 'sylius/loyalty-plugin'], true)) {
                $args[] = '--no-scripts';
            }
            $process = new Process($args);
            $process->setTty(Process::isTtySupported());
            $process->run();
            if (!$process->isSuccessful()) {
                $io->error("Failed to install $pkg:\n" . $process->getErrorOutput());
                return Command::FAILURE;
            }
        }

        // Plugin-specific post steps
        // CMS Plugin
        if (isset($data['plugins']['sylius/cms-plugin'])) {
            $io->section('Running CMS post-install steps');
            Process::fromShellCommandline('composer config extra.symfony.allow-contrib true')->run();
            Process::fromShellCommandline('yarn add trix@^2.0.0 swiper@^11.2.6')->run();
        }

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

        // Final common steps
        // Remove existing cache directories to avoid stale container errors
        $io->section('Removing existing cache directories');
        $filesystem->remove([
            getcwd() . '/var/cache/dev',
            getcwd() . '/var/cache/prod',
        ]);

        $io->section('Running database schema sync');
        $migrateProc = Process::fromShellCommandline('bin/console doctrine:schema:update --force --complete');
        $migrateProc->run();
        if (!$migrateProc->isSuccessful()) {
            $io->error('Database sync failed: ' . $migrateProc->getErrorOutput());
            return Command::FAILURE;
        }

        $io->section('Installing assets and building front');
        Process::fromShellCommandline('bin/console assets:install')->run();
        Process::fromShellCommandline('yarn encore dev')->run();

        $io->section('Clearing and warming up cache');
        Process::fromShellCommandline('bin/console cache:clear')->run();
        Process::fromShellCommandline('bin/console cache:warmup')->run();

        $io->success('All plugins installed and configured successfully.');

        $io->section('Default fixtures');
        $fixturesProcess = Process::fromShellCommandline('bin/console sylius:fixtures:load --no-interaction');;
        $fixturesProcess->setTty(Process::isTtySupported());
        $fixturesProcess->run();
        if (!$fixturesProcess->isSuccessful()) {
            $io->error('Fixtures load failed: ' . $fixturesProcess->getErrorOutput());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
