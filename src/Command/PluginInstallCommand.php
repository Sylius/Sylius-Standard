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
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'sylius:plugin:finalize-installation',
    description: 'Finalize plugin installation steps'
)]
class FinalizePluginInstallationCommand extends Command
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

        $io->title('Run installers for plugins:');
        foreach ($data['plugins'] as $pkg => $version) {
            $io->info("Available installers for : " . count($this->installers));
            foreach ($this->installers as $installer) {
                if ($installer->supports($pkg)) {
                    $io->section("Post-install for $pkg");
                    $installer->install($io);
                    break;
                }
            }
        }

        foreach ($data['plugins'] as $pkg => $version) {
            $io->info("Available installers for : " . count($this->installers));
            foreach ($this->installers as $installer) {
                if ($installer->supports($pkg)) {
                    $io->section("Post-install for $pkg");
                    $installer->finalize($io);
                    break;
                }
            }
        }

        $io->section('Running Rector');
        $process = Process::fromShellCommandline('vendor/bin/rector process src');
        $process->run();


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


        $io->section('Loading default fixtures');
        $fixtures = Process::fromShellCommandline('bin/console sylius:fixtures:load --no-interaction');
        $fixtures->setTty(Process::isTtySupported());
        $fixtures->run();
        if (!$fixtures->isSuccessful()) {
            $io->error('Fixtures load failed: ' . $fixtures->getErrorOutput());
            return Command::FAILURE;
        }



        return Command::SUCCESS;
    }
}
