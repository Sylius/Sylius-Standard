<?php

declare(strict_types=1);

namespace App\Command\Plugin;

use App\Command\ConfigTrait;
use RuntimeException;
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
    name: 'sylius:dx:plugin:install',
    description: 'Require and install Sylius plugins in one go'
)]
class PluginInstall extends Command
{
    use ConfigTrait;

    protected static $defaultName = 'sylius:dx:plugin:install';

    private SymfonyStyle $io;

    public function __construct(
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
        #[AutowireIterator('app.plugin_installer')] private readonly iterable $installers,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('store', InputOption::VALUE_REQUIRED, 'Name of the store directory under store-creator/')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $store = $input->getArgument('store');
        $this->validateStore($store);
        $plugins = $this->getPluginsByStore($store);

        $this->io->section('[Plugin Installer] Installing plugins');
        foreach (array_keys($plugins) as $plugin) {
            $installer = $this->findInstallerFor($plugin);
            $installer->install($this->io);
        }

        $this->io->section('[Plugin Installer] Running assets build');
        $this->runCommand(['yarn', 'encore', 'production']);

        $this->io->success('All plugins processed successfully.');

        return Command::SUCCESS;
    }

    private function findInstallerFor(string $plugin)
    {
        foreach ($this->installers as $installer) {
            if ($installer->supports($plugin)) {
                return $installer;
            }
        }

        throw new RuntimeException(sprintf('No installer found for package "%s"', $plugin));
    }

    private function runCommand(array $command): int
    {
        $process = new Process($command, $this->projectDir);
        $process
            ->setTty(Process::isTtySupported())
            ->setTimeout(0)
            ->mustRun(fn(string $type, string $buffer) => $this->io->write($buffer));

        return $process->getExitCode();
    }
}
