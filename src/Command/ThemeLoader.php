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
    name: 'sylius:dx:theme-loader',
    description: 'Load themes from configuration and generate stylesheets',
)]
class ThemeLoader extends Command
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
        $this
            ->addArgument('store', InputArgument::REQUIRED, 'Name of the store directory under store-creator/')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite existing theme files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $store = (string) $input->getArgument('store');
        $force = (bool) $input->getOption('force');

        $io->title(sprintf('Sylius Theme Loader (store: %s)', $store));

        // 1) Load themes configuration from store-creator/<store>/store-creator.json
        $configPath = sprintf('%s/store-creator/%s/store-creator.json', $this->projectDir, $store);
        if (!file_exists($configPath)) {
            $io->error(sprintf('Configuration file not found: %s', $configPath));
            return Command::FAILURE;
        }

        try {
            $raw = file_get_contents($configPath);
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            $io->error('Invalid JSON in store-creator.json: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $themes = $data['themes'] ?? [];
        if (empty($themes)) {
            $io->warning('No themes defined in configuration.');
            return Command::SUCCESS;
        }

        $io->text('Loaded theme areas: ' . implode(', ', array_keys($themes)));

        // 2) For each area (e.g., "shop", "admin"), generate custom-theme.scss under assets/<area>/styles
        foreach ($themes as $area => $themeConfig) {
            $relativeStylesDir = sprintf('assets/%s/styles', $area);
            $stylesDir = $this->projectDir . '/' . ltrim($relativeStylesDir, '/');
            if (!is_dir($stylesDir) && !mkdir($stylesDir, 0755, true) && !is_dir($stylesDir)) {
                $io->error(sprintf('Failed to create styles directory: %s', $stylesDir));
                continue;
            }

            // Build :root { --var: value; ... } block
            $themeFile = $stylesDir . '/custom-theme.scss';
            if (file_exists($themeFile) && !$force) {
                $io->warning(sprintf('SCSS theme file already exists, skipping: %s', $themeFile));
            } else {
                $variables = $themeConfig['cssVariables'] ?? [];
                $lines = [":root {"];
                foreach ($variables as $name => $value) {
                    $lines[] = sprintf('    %s: %s;', $name, $value);
                }
                $lines[] = "}";

                file_put_contents($themeFile, implode("\n", $lines) . "\n");
                $io->success(sprintf('Generated theme file: %s', $themeFile));
            }

            // Append import in assets/<area>/entrypoint.js
            $entryFile = $this->projectDir . sprintf('/assets/%s/entrypoint.js', $area);
            if (file_exists($entryFile)) {
                $importLine = "import './styles/custom-theme.scss';";
                $content = file_get_contents($entryFile);
                if (strpos($content, $importLine) === false) {
                    $content = rtrim($content, "\n") . "\n" . $importLine . "\n";
                    file_put_contents($entryFile, $content);
                    $io->success(sprintf('Appended SCSS import to %s', $entryFile));
                } else {
                    $io->text(sprintf('Import already present in %s', $entryFile));
                }
            } else {
                $io->warning(sprintf('entrypoint.js not found for area "%s": %s', $area, $entryFile));
            }
        }

        // 3) Load and copy logo(s) from store-creator/<store>/themes/<area>/logo.png
        $io->section('Loading logo assets');
        foreach ($themes as $area => $themeConfig) {
            $io->section(sprintf('Processing area: %s', $area));
            if (empty($themeConfig['logo'])) {
                $io->text(sprintf('No logo defined for area "%s", skipping.', $area));
                continue;
            }

            $logoFilename = $themeConfig['logo'];
            $logoSrc = sprintf('%s/store-creator/%s/themes/%s/%s', $this->projectDir, $store, $area, $logoFilename);
            if (!file_exists($logoSrc)) {
                $io->warning(sprintf('Logo file for area "%s" not found at path: %s', $area, $logoSrc));
                continue;
            }

            $logoSrc = sprintf('%s/store-creator/%s/themes/%s/%s', $this->projectDir, $store, $area, $logoFilename);

            $assetsImagesDir = $this->projectDir . sprintf('/assets/%s/images', $area);
            if (!is_dir($assetsImagesDir) && !mkdir($assetsImagesDir, 0755, true) && !is_dir($assetsImagesDir)) {
                $io->error(sprintf('Failed to create assets images directory: %s', $assetsImagesDir));
                continue;
            }

            $destLogoInAssets = $assetsImagesDir . '/' . $area . '-logo.' . pathinfo($logoFilename, PATHINFO_EXTENSION);
            if (file_exists($destLogoInAssets) && !$force) {
                $io->warning(sprintf('Logo already exists in assets, overwriting: %s', $destLogoInAssets));
            }

            copy($logoSrc, $destLogoInAssets);
            $io->success(sprintf('Copied logo for area "%s" to assets: %s', $area, $destLogoInAssets));

            // Create Twig template under templates/shared/logo-<area>.html.twig
            $templatesDir = $this->projectDir . '/templates/shared';
            if (!is_dir($templatesDir) && !mkdir($templatesDir, 0755, true) && !is_dir($templatesDir)) {
                $io->error(sprintf('Failed to create templates/shared directory: %s', $templatesDir));
                continue;
            }

            $templateName = sprintf('logo-%s.html.twig', $area);
            $templatePath = $templatesDir . '/' . $templateName;
            $assetPath = sprintf('images/%s-logo.%s', $area, pathinfo($logoFilename, PATHINFO_EXTENSION));

            $twigContent = <<<TWIG
{# templates/shared/{$templateName} #}
<a href="{{ path('sylius_{$area}_homepage') }}" class="app-{$area}-logo">
    <img src="{{ asset('{$assetPath}') }}" alt="Logo {$area}" />
</a>
TWIG;

            if (file_exists($templatePath) && !$force) {
                $io->warning(sprintf('Twig template already exists, overwriting: %s', $templatePath));
            }

            file_put_contents($templatePath, $twigContent . "\n");
            $io->success(sprintf('Created Twig logo template: %s', $templatePath));

        }

        // 4) Ensure config/packages/sylius_twig_hooks.yaml defines the proper hooks
        $io->section('Updating Twig hook configuration');
        $hooksConfigPath = $this->projectDir . '/config/packages/sylius_twig_hooks.yaml';

        // Base structure if file does not exist
        if (!file_exists($hooksConfigPath)) {
            $baseConfig = <<<YAML
sylius_twig_hooks:
    hooks:
YAML;
            file_put_contents($hooksConfigPath, $baseConfig . "\n");
            $io->success(sprintf('Created new hook config: %s', $hooksConfigPath));
        }

        // Load existing content
        $hooksContent = file_get_contents($hooksConfigPath);

        // For each area with a logo, append hook if missing
        foreach ($themes as $area => $themeConfig) {
            if (empty($themeConfig['logo'])) {
                continue;
            }

            // Determine hook name and template reference
            // For "shop" area, we hook into sylius_shop.base.header.content.logo
            // For "admin" area, we hook into sylius_admin.base.header (example)
            if ($area === 'shop') {
                $hookKey = 'sylius_shop.base.header.content.logo';
                $twigTemplate = sprintf('shared/logo-%s.html.twig', $area);
            } else {
                // You may adjust the admin-area hook key as needed
                $hookKey = 'sylius_admin.layout.header';
                $twigTemplate = sprintf('shared/logo-%s.html.twig', $area);
            }

            // Check if this hook is already present
            if (strpos($hooksContent, $hookKey) !== false) {
                $io->text(sprintf('Hook "%s" already present, skipping.', $hookKey));
                continue;
            }

            // Build YAML snippet to append
            $snippet = <<<YAML

        '{$hookKey}':
            content:
                template: '{$twigTemplate}'
                priority: 0
YAML;

            // Append under "hooks:" (2 spaces indentation for keys)
            // We assume file already ends at "hooks:" or existing hooks
            $updated = preg_replace(
                '/(sylius_twig_hooks:\s*\n\s*hooks:\s*)/m',
                "\$1" . $snippet,
                $hooksContent,
                1
            );

            if ($updated === null) {
                $io->error('Failed to update hooks config (preg_replace error).');
            } else {
                file_put_contents($hooksConfigPath, $updated);
                $hooksContent = $updated;
                $io->success(sprintf('Appended hook "%s" to %s', $hookKey, $hooksConfigPath));
            }
        }

        // 5) Rebuild assets
        $io->section('Building assets with Webpack Encore');
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
