<?php

declare(strict_types=1);

namespace App\Command;

use App\Plugin\Installer\PluginInstallerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'sylius:plugin-installer:finalize',
    description: 'Finalize plugin installation steps'
)]
class PluginFinalizeCommand extends Command
{
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


        $configPath = 'booster.json';
        if (!file_exists($configPath)) {
            $io->error("Configuration file '$configPath' not found.");
            return Command::FAILURE;
        }
        $data = json_decode(file_get_contents($configPath), true);
        if (!isset($data['plugins']) || !is_array($data['plugins'])) {
            $io->error('Invalid config: missing "plugins" array.');
            return Command::FAILURE;
        }


        $io->title('Check GIT diff before finalization:');
        $io->text(Process::fromShellCommandline('git status')->mustRun()->getErrorOutput());

        $io->title('Run installers for plugins:');
        foreach ($data['plugins'] as $pkg => $version) {
            $io->info('Available finalizers for "' . $pkg . '": ' . count($this->installers));
            foreach ($this->installers as $installer) {
                if ($installer->supports($pkg)) {
                    $io->info("Finalizing installation for $pkg");
                    $installer->finalize($io);
                    break;
                }
            }
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
