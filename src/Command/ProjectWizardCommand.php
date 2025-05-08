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
        // Multi Source Inventory
        if (isset($data['plugins']['sylius/multi-source-inventory-plugin'])) {
            $io->section('Applying Multi Source Inventory plugin recipes');
            $rectorFile = getcwd() . '/rector.php';
            if (file_exists($rectorFile)) {
                $content = file_get_contents($rectorFile);
                if (strpos($content, 'MULTI_SOURCE_INVENTORY_PLUGIN') === false) {
                    $insertion = "    \$rectorConfig->sets([\n        SyliusPlus::MULTI_SOURCE_INVENTORY_PLUGIN,\n    ]);\n";
                    $content = str_replace(');', $insertion . ');', $content);
                    file_put_contents($rectorFile, $content);
                    $io->text('Updated rector.php with MULTI_SOURCE_INVENTORY_PLUGIN set');
                }
            }
            $pkgConfig = getcwd() . '/config/packages/sylius_multi_source_inventory_plugin.yaml';
            $yaml = <<<YAML
imports:
    - { resource: "@SyliusMultiSourceInventoryPlugin/src/Integration/CustomerService/Resources/config/parameters.yaml" }
parameters:
    sylius.form.type.add_to_cart.validation_groups:
        - sylius_multi_source_inventory
YAML;
            $filesystem->dumpFile($pkgConfig, $yaml);
            $io->text('Created config/packages/sylius_multi_source_inventory_plugin.yaml');
        }
        // Loyalty Plugin
        if (isset($data['plugins']['sylius/loyalty-plugin'])) {
            $io->section('Applying Loyalty Plugin recipes');
            $rectorFile = getcwd() . '/rector.php';
            if (file_exists($rectorFile)) {
                $content = file_get_contents($rectorFile);
                if (strpos($content, 'LOYALTY_PLUGIN') === false) {
                    $insertion = "    \$rectorConfig->sets([\n        SyliusPlus::LOYALTY_PLUGIN,\n    ]);\n";
                    $content = str_replace(');', $insertion . ');', $content);
                    file_put_contents($rectorFile, $content);
                    $io->text('Updated rector.php with LOYALTY_PLUGIN set');
                }
            }
        }

        // Final common steps
        // Remove existing cache directories to avoid stale container errors
        $io->section('Removing existing cache directories');
        $filesystem->remove([
            getcwd() . '/var/cache/dev',
            getcwd() . '/var/cache/prod',
        ]);

        $io->section('Running database migrations');
        Process::fromShellCommandline('bin/console doctrine:migrations:migrate --no-interaction')->run();

        $io->section('Installing assets and building front');
        Process::fromShellCommandline('bin/console assets:install')->run();
        Process::fromShellCommandline('yarn encore dev')->run();

        $io->section('Clearing and warming up cache');
        Process::fromShellCommandline('bin/console cache:clear')->run();
        Process::fromShellCommandline('bin/console cache:warmup')->run();

        $io->success('All plugins installed and configured successfully.');
        return Command::SUCCESS;
    }
}
