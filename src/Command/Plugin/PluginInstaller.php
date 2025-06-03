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
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'sylius:dx:plugin:installer',
    description: 'Require and install Sylius plugins in one go'
)]
class PluginInstaller extends Command
{
    use ConfigTrait;

    protected static $defaultName = 'sylius:dx:plugin:preparer';

    private SymfonyStyle $io;

    public function __construct(
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('store', null, InputOption::VALUE_OPTIONAL, 'Load plugins from store-creator/{store}/store-creator.json')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $store = $input->getOption('store');
        $this->validateStore($store);
        $plugins = $this->getPluginsByStore($store);

        Process::fromShellCommandline('composer config extra.symfony.allow-contrib true')->setTimeout(0)->run();
        Process::fromShellCommandline('composer config repositories.sylius composer https://sylius.repo.packagist.com/sylius/')->setTimeout(0)->run();

        $this->io->section('[Plugin Preparer] Installing plugins');
        foreach ($plugins as $package => $version) {
            Process::fromShellCommandline("composer require $package:$version --no-scripts --no-interaction")
                ->setTimeout(0)
                ->mustRun(fn($type, $buffer) => $output->write($buffer));
            Process::fromShellCommandline("composer require $package:dev-booster --no-scripts --no-interaction")
                ->setTimeout(0)
                ->mustRun(fn($type, $buffer) => $output->write($buffer));
        }

            return Command::SUCCESS;

        $this->io->title('Running Rector');
        $this->runCommand(['vendor/bin/rector', 'process', 'src']);

        $this->io->title('Installing plugins');
        foreach (array_keys($plugins) as $plugin) {
            $installer = $this->findInstallerFor($plugin);
            $installer->install($this->io);
        }

        $this->runCommonPostSteps();
        $this->io->success('All plugins processed successfully.');

        return Command::SUCCESS;
    }

    private function findInstallerFor(mixed $plugin)
    {
        foreach ($this->installers as $installer) {
            if ($installer->supports($plugin)) {
                return $installer;
            }
        }

        throw new RuntimeException(sprintf('No installer found for package "%s"', $plugin));
    }

    private function runCommonPostSteps(): void
    {
        $this->io->title('Installing assets and building front');
        $this->runCommand(['bin/console', 'assets:install', '-n', '--no-debug']);
        $this->runCommand(['yarn', 'encore', 'production']);

        $this->io->section('Running database sync');
        $this->runCommand(['bin/console', 'doctrine:schema:update', '-n', '--force', '--complete', '--no-debug']);

//        $this->io->section('Loading default fixtures');
//        $this->runCommand(['bin/console', 'sylius:fixtures:load', '-n', '--no-debug'], $this->io);

        $this->io->success('All plugins installed and configured successfully.');
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
