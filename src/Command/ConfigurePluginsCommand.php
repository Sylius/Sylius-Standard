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
    name: 'project:configure-plugins',
    description: 'Runs post-install configuration steps for Sylius plugins'
)]
class ConfigurePluginsCommand extends Command
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

        $io->title('Running post-install steps for plugins');
        foreach ($data['plugins'] as $pkg => $version) {
            foreach ($this->installers as $installer) {
                if ($installer->supports($pkg)) {
                    $io->section("Post-install for $pkg");
                    $installer->install($version, $input, $output);
                    break;
                }
            }
        }

        //rector
        $io->section('Running Rector');
        $process = Process::fromShellCommandline('vendor/bin/rector process src');
        $process->run();

        $io->success('All post-install steps completed.');
        return Command::SUCCESS;
    }
}
