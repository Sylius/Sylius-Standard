<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
            ->addArgument('store', InputArgument::REQUIRED, 'Name of the store directory under store-creator/')
            ->addOption('build', null, InputArgument::OPTIONAL, 'Build the store before loading', false)
            ->addOption('deploy', null, InputArgument::OPTIONAL, 'Deploy the store after loading', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $store = $input->getArgument('store');
        try {
            $this->validateStore($store);
        } catch (\RuntimeException $e) {
            $this->io->error($e->getMessage());
            return Command::FAILURE;
        }

        $this->io->title(sprintf('Creating store: %s', $store));

        if ($input->getOption('build') === false && $input->getOption('deploy') === false) {
            $this->io->error('You must specify at least one of the options: --build or --deploy');
            return Command::FAILURE;
        }

        if ($input->getOption('build')) {
            $this->io->section('[Store Loader] BUILD');
            $this->build($store);
        }

        if ($input->getOption('deploy')) {
            $this->io->section('[Store Loader] DEPLOY');
            $this->deploy($store);
        }

//        $this->io->section('[Store Loader] FIXTURES');
//        $this->runCommand(['php', 'bin/console', 'sylius:dx:fixture-loader', $store, '--no-debug']);
//
//        $this->io->section('[Store Loader] THEMES');
//        $this->runCommand(['composer', 'require', 'intervention/image']);
//        $this->runCommand(['php', 'bin/console', 'sylius:dx:theme-loader', $store, '--no-debug']);

        $this->io->success('Store creation complete!');

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

    public function build(string $store): void
    {
        $this->io->section('[Store Loader] PLUGINS');

        $this->runCommand(['php', 'bin/console', 'sylius:dx:plugin:prepare', $store]);
        $this->runCommand(['php', 'bin/console', 'sylius:dx:plugin:install', $store]);
    }

    public function deploy(string $store): void
    {
        // TODO: Implement deploy() method.
    }
}
