<?php

declare(strict_types=1);

namespace App\Command\Plugin;

use App\Command\ConfigTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'sylius:dx:plugin:prepare',
    description: 'Require Sylius plugins in one go'
)]
class PluginPrepare extends Command
{
    use ConfigTrait;

    protected static $defaultName = 'sylius:dx:plugin:prepare';

    private SymfonyStyle $io;

    public function __construct(#[Autowire('%kernel.project_dir%')] private readonly string $projectDir)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('store', InputOption::VALUE_REQUIRED, 'Name of the store directory under store-preset/')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $store = $input->getArgument('store');
        $this->validateStore($store);
        $plugins = $this->getPluginsByStore($store);

        Process::fromShellCommandline('composer config extra.symfony.allow-contrib true')->setTimeout(0)->run();
        Process::fromShellCommandline('composer config repositories.sylius composer https://sylius.repo.packagist.com/sylius/')->setTimeout(0)->run();

        $this->io->section('[Plugin Preparer] Installing plugins');
        foreach ($plugins as $package => $version) {
            // Necessary to resolve Symfony recipes correctly
            Process::fromShellCommandline("composer require $package:$version --no-scripts --no-interaction")
                ->setTimeout(0)
                ->mustRun(fn($type, $buffer) => $output->write($buffer));
            // Install dev-booster version for development purposes
            Process::fromShellCommandline("composer require $package:dev-booster --no-scripts --no-interaction")
                ->setTimeout(0)
                ->mustRun(fn($type, $buffer) => $output->write($buffer));
        }

        $this->io->title('[Plugin Preparer] Running Rector');
        $this->runCommand(['vendor/bin/rector', 'process', 'src']);

        $this->io->title('[Plugin Preparer] Running assets installation');
        $this->runCommand(['bin/console', 'assets:install', '-n']);

        $this->io->success('[Plugin Preparer] Plugins installed successfully.');

        return Command::SUCCESS;
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
