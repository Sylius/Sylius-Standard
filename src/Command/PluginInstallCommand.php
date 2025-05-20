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
    description: 'Finalize plugin installation steps based on PLATFORM_DEMO_PLUGINS_JSON'
)]
class PluginInstallCommand extends Command
{
    private const ENV_PLUGINS = 'SYLIUS_PLUGINS_JSON';

    /** @param iterable<PluginInstallerInterface> $installers */
    public function __construct(
        #[TaggedIterator('app.plugin_installer')]
        private readonly iterable $installers
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $rawJson = getenv(self::ENV_PLUGINS);
        if (false === $rawJson || '' === trim($rawJson)) {
            $io->error(sprintf('Environment variable %s is not set or empty.', self::ENV_PLUGINS));
            return Command::FAILURE;
        }

        try {
            $plugins = json_decode($rawJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $io->error(sprintf('Invalid JSON in %s: %s', self::ENV_PLUGINS, $e->getMessage()));
            return Command::FAILURE;
        }

        if (!is_array($plugins)) {
            $io->error(sprintf('%s JSON does not decode to an array.', self::ENV_PLUGINS));
            return Command::FAILURE;
        }

        $io->title('Run installers for plugins:');
        foreach ($plugins as $package => $version) {
            $io->info('Available installers for "' . $package . '": ' . count($this->installers));
            foreach ($this->installers as $installer) {
                if ($installer->supports($package)) {
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

        $io->success('Plugin installation workflow completed.');

        return Command::SUCCESS;
    }
}
