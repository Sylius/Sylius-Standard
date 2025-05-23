<?php

declare(strict_types=1);

namespace App\Command;

use App\Plugin\Installer\PluginInstallerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\DependencyInjection\Exception\EnvNotFoundException;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'sylius:plugin-manager',
    description: 'List, install or uninstall Sylius plugins'
)]
class PluginManagerCommand extends Command
{
    use PluginConfigTrait;

    protected function configure(): void
    {
        $this
            ->addArgument('action', InputArgument::OPTIONAL, 'install|uninstall|list', null)
            ->addArgument('plugins', InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                'One or more plugin names (e.g. sylius/return-plugin)')
            ->addOption('no-interaction', 'n', InputOption::VALUE_NONE,
                'Run in non-interactive (automated) mode');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $action = $input->getArgument('action');
        $choices = $input->getArgument('plugins');
        $nonInt = $input->getOption('no-interaction');

        // 1) Zebranie mapy wszystkich dostępnych instalatorów
        $map = $this->collectInstallers(); // ['sylius/return-plugin' => $installer, ...]

        // 2) Tryb interaktywny
        if (!$nonInt && $action === null) {
            // a) lista i status
            $rows = [];
            foreach ($map as $name => $installer) {
                $rows[] = [
                    $name,
                    in_array($name, $this->loadEnabledPlugins()) ? '✅ enabled' : '—',
                    $installer->getMeta()['description'],
                ];
            }
            $io->table(['Plugin', 'Status', 'Description'], $rows);

            // b) wybór akcji
            $action = $io->choice(
                'What do you want to do?',
                ['list', 'install', 'uninstall', 'quit'],
                'quit'
            );
            if ($action === 'quit') {
                return Command::SUCCESS;
            }

            // c) wybór pluginów
            $default = [];
            if ($action === 'uninstall') {
                $default = $this->loadEnabledPlugins();
            }
            $choices = $io->multiselect(
                sprintf('Select plugins to %s', $action),
                array_keys($map),
                $default
            );
        }

        // 3) Tryb nie-interaktywny wymaga podania action + co najmniej jednego pluginu
        if ($nonInt && ($action === null || empty($choices))) {
            $io->error('In automated mode you must specify an action and at least one plugin.');
            return Command::FAILURE;
        }

        // 4) Wykonanie ruchu
        switch ($action) {
            case 'list':
                // już pokazaliśmy w tabeli -> nic więcej
                break;

            case 'install':
                $this->pipelineInstall($choices, $io);
                break;

            case 'uninstall':
                $this->pipelineUninstall($choices, $io);
                break;
        }

        return Command::SUCCESS;
    }

}
