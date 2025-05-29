<?php

declare(strict_types=1);

namespace App\Command;

use RuntimeException;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'sylius:theme-creator',
    description: 'Manage Sylius themes',
)]
class ThemeCreator extends Command
{
    use ConfigTrait;

    private string $projectDir;

    public function __construct(KernelInterface $kernel)
    {
        parent::__construct();
        $this->projectDir = $kernel->getProjectDir();
    }

    protected function configure(): void
    {
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite existing theme files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Sylius Theme Creator');

        $themes = $this->loadThemes($io);
        if (empty($themes)) {
            $io->error('No themes found in configuration.');
            return Command::FAILURE;
        }
        $io->text('Loaded themes: ' . implode(', ', array_keys($themes)));
        $force = (bool) $input->getOption('force');

        foreach ($themes as $area => $themeConfig) {
            $relativeDir = sprintf('assets/%s/styles', $area);
            $stylesDir = $this->projectDir . '/' . ltrim($relativeDir, '/');
            if (!is_dir($stylesDir) && !mkdir($stylesDir, 0755, true) && !is_dir($stylesDir)) {
                $io->error(sprintf('Failed to create styles directory: %s', $stylesDir));
                continue;
            }

            // Generate custom-theme.scss with CSS custom properties under :root
            $themeFile = $stylesDir . '/custom-theme.scss';
            if (file_exists($themeFile) && !$force) {
                $io->warning(sprintf('Theme file exists, skipping: %s', $themeFile));
            } else {
                $variables = $themeConfig['cssVariables'] ?? [];
                $lines = [":root {"];
                foreach ($variables as $name => $value) {
                    // keep CSS var syntax
                    $lines[] = sprintf('    %s: %s;', $name, $value);
                }
                $lines[] = "}";

                file_put_contents($themeFile, implode("\n", $lines) . "\n");
                $io->success(sprintf('Generated theme file: %s', $themeFile));
            }

            // Update entrypoint.js to import generated theme
            $entry = $this->projectDir . sprintf('/assets/%s/entrypoint.js', $area);
            if (file_exists($entry)) {
                $import = "import './styles/custom-theme.scss';";
                $content = file_get_contents($entry);
                if (strpos($content, $import) === false) {
                    $content = rtrim($content, "\n") . "\n" . $import . "\n";
                    file_put_contents($entry, $content);
                    $io->success(sprintf('Appended theme import to entrypoint.js for %s', $area));
                } else {
                    $io->text(sprintf('Theme import already present for %s', $area));
                }
            } else {
                $io->warning(sprintf('entrypoint.js not found: %s', $entry));
            }
        }

        // Rebuild assets
        $io->text('Building assets with Webpack Encore...');
        $process = Process::fromShellCommandline('yarn encore dev', $this->projectDir);
        $process->run(fn($type, $buffer) => $io->write($buffer));
        if (!$process->isSuccessful()) {
            $io->error('Asset build failed.');
            return Command::FAILURE;
        }
        $io->success('Assets built successfully.');

        return Command::SUCCESS;
    }
}
