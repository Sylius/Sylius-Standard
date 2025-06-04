<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'sylius:dx:store-loader',
    description: 'Orchestrate Sylius installation: plugins, fixtures, themes',
)]
class StoreLoader extends Command implements BuildAndDeployContextSeparatorInterface
{
    use ConfigTrait;

    private string $projectDir;

    private SymfonyStyle $io;

    public function __construct(KernelInterface $kernel)
    {
        parent::__construct();
        $this->projectDir = $kernel->getProjectDir();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Orchestrate Sylius installation: plugins, fixtures, themes')
            ->addArgument('store', InputArgument::OPTIONAL, 'Name of the store directory under store-preset/')
            ->addOption('build', null, InputOption::VALUE_NONE, 'Build the store before loading')
            ->addOption('deploy', null, InputOption::VALUE_NONE, 'Deploy the store after loading');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $store = $this->getStoreName();

        $this->io->title(sprintf('Creating store: %s', $store));

        if ($input->getOption('build') === false && $input->getOption('deploy') === false) {
            $this->io->error('You must specify at least one of the options: --build or --deploy');
            return Command::FAILURE;
        }

        if ($input->getOption('build')) {
            $this->io->section('[Store Loader] BUILD');
            if (empty($store)) {
                $this->io->success('Store preset missing. To use this command, you must specify a store name as an argument or ensure that the store-preset/store-preset.json file exists with a valid store name.');
                return Command::SUCCESS;
            }
            $this->build($store);
            $this->io->success('[Store Loader] BUILD completed successfully.');
        }

        if ($input->getOption('deploy')) {
            $this->io->section('[Store Loader] DEPLOY');
            $this->deploy($store);
            $this->io->success('[Store Loader] DEPLOY completed successfully.');
        }

        return Command::SUCCESS;
    }

    public function build(string $store): void
    {
        $this->io->section('[Store Loader] PLUGINS');

        $this->runCommand(['php', 'bin/console', 'sylius:dx:plugin:prepare', $store]);
        $this->runCommand(['php', 'bin/console', 'cache:clear', '--no-warmup']);
        $this->runCommand(['php', 'bin/console', 'cache:warmup']);
        $this->runCommand(['php', 'bin/console', 'sylius:dx:plugin:install', $store]);


        $this->io->section('[Store Loader] FIXTURES');
        $this->runCommand(['php', 'bin/console', 'sylius:dx:fixture:prepare', $store]);

        $this->io->section('[Store Loader] THEMES');
        $this->runCommand(['php', 'bin/console', 'cache:clear', '--no-warmup']);
        $this->runCommand(['php', 'bin/console', 'cache:warmup']);

        $this->runCommand(['php', 'bin/console', 'sylius:dx:theme:prepare', $store]);
    }

    public function deploy(?string $store = null): void
    {
        $this->io->section('[Store Loader] PLUGINS');

        $this->io->info('[Plugin Installer] Running database schema update');
        $this->runCommand(['bin/console', 'doctrine:database:drop', '--if-exists', '-n', '--force']);
        $this->runCommand(['bin/console', 'doctrine:database:create', '-n']);
        $this->runCommand(['bin/console', 'doctrine:schema:update', '-n', '--force', '--complete']);

        if ($store === null) {
            $this->io->info(['Store Loader] No store specified, loading default suite']);
            $this->runCommand(['php', 'bin/console', 'sylius:fixtures:load', '-n']);

            return;
        }

        $this->io->section('[Store Loader] FIXTURES');
        $this->runCommand(['php', 'bin/console', 'sylius:dx:fixture:load']);
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
