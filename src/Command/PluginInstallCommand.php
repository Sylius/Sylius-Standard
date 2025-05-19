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
    name: 'sylius:plugin-installer:install',
    description: 'Finalize plugin installation steps'
)]
class PluginInstallCommand extends Command
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
        $io->section('Command 1');

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

        $io->title('Run installers for plugins:');
        foreach ($data['plugins'] as $pkg => $version) {
            $io->info('Available installers for "' . $pkg . '": ' . count($this->installers));
            foreach ($this->installers as $installer) {
                if ($installer->supports($pkg)) {
                    $io->info("Installer found for $pkg");
                    $installer->install($io);
                    break;
                }
            }
        }

        $io->title('Running Rector');
        $process = Process::fromShellCommandline('vendor/bin/rector process src');
        $process->run();

        $io->title('Installing assets and building front');
        Process::fromShellCommandline('bin/console assets:install')->run();
        Process::fromShellCommandline('yarn encore dev')->run();



        $warmup = new Process(['bin/console', 'cache:warmup'], getcwd());
        $warmup->run();
        if (!$warmup->isSuccessful()) {
            $io->warning('Cache warmup failed: ' . $warmup->getErrorOutput());
        }

        return Command::SUCCESS;
    }
}
