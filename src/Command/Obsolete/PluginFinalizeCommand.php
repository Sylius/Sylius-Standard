<?php

declare(strict_types=1);

namespace App\Command\Obsolete;

use App\Plugin\Installer\PluginInstallerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\DependencyInjection\Exception\EnvNotFoundException;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'sylius:plugin-installer:finalize',
    description: 'Finalize plugin installation steps'
)]
class PluginFinalizeCommand extends Command
{
    use PluginConfigTrait;

    /** @var iterable<PluginInstallerInterface> */
    private iterable $installers;

    public function __construct(
        #[TaggedIterator('app.plugin_installer')]
        iterable $installers
    ) {
        parent::__construct();
        $this->installers = $installers;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Command 2');

        $warmup = new Process(['bin/console', 'cache:warmup'], getcwd());
        $warmup->run();
        if (!$warmup->isSuccessful()) {
            $io->warning('Cache warmup failed: ' . $warmup->getErrorOutput());
        }

        $io->section('Running database sync');
        $sync = Process::fromShellCommandline('bin/console doctrine:schema:update --force --complete');
        $sync->run();
        if (!$sync->isSuccessful()) {
            $io->error('Database sync failed: ' . $sync->getErrorOutput());
            return Command::FAILURE;
        }

        $io->section('Loading default fixtures');
        $fixtures = Process::fromShellCommandline('bin/console sylius:fixtures:load --no-interaction');
        $fixtures->setTty(Process::isTtySupported());
        $fixtures->run();
        if (!$fixtures->isSuccessful()) {
            $io->error('Fixtures load failed: ' . $fixtures->getErrorOutput());
            return Command::FAILURE;
        }

        $io->title('Run installers for plugins:');
        try {
            $plugins = $this->loadPlugins($io);
        } catch (EnvNotFoundException) {
            $io->info('No plugins to process.');

            return Command::SUCCESS;
        }
        foreach ($plugins as $pkg => $version) {
            $io->info('Available finalizers for "' . $pkg . '": ' . count($this->installers));
            foreach ($this->installers as $installer) {
                if ($installer->supports($pkg)) {
                    $io->info("Finalizing installation for $pkg");
                    $installer->finalize($io);
                    break;
                }
            }
        }

        $io->section('Running cache warmup in a fresh process');
        $warmup = new Process(['bin/console', 'cache:warmup'], getcwd());
        $warmup->run();
        if (!$warmup->isSuccessful()) {
            $io->warning('Cache warmup failed: ' . $warmup->getErrorOutput());
        }

        $io->success('All plugins installed and configured successfully.');

        return Command::SUCCESS;
    }
}
