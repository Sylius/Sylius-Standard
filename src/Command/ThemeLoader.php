<?php

declare(strict_types=1);

namespace App\Command;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
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
    description: 'Load themes from configuration and generate stylesheets (SCSS + logo assets)',
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
        $io    = new SymfonyStyle($input, $output);
        $store = (string)$input->getArgument('store');
        $force = (bool)$input->getOption('force');

        $io->title(sprintf('Sylius Theme Loader (store: %s)', $store));

        //
        // 1) Ładowanie konfiguracji JSON
        //
        $configPath = sprintf('%s/store-creator/%s/store-creator.json', $this->projectDir, $store);
        if (!file_exists($configPath)) {
            $io->error(sprintf('Configuration file not found: %s', $configPath));
            return Command::FAILURE;
        }

        try {
            $raw  = file_get_contents($configPath);
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            $io->error('Invalid JSON in store-creator.json: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $themes = $data['themes'] ?? [];
        if (empty($themes)) {
            $io->warning('No themes defined in configuration.');
            return Command::SUCCESS;
        }

        $io->text('Loaded theme areas: ' . implode(', ', array_keys($themes)));

        //
        // 2) Dla każdego obszaru („area”: shop, admin, itd.) generujemy SCSS i kopiujemy plik logo do assets/<area>/images/
        //
        foreach ($themes as $area => $themeConfig) {
            $io->section(sprintf('Processing theme variables and logo for area: %s', $area));

            // 2a) Generacja custom-theme.scss → assets/<area>/styles/custom-theme.scss
            $relativeStylesDir = sprintf('assets/%s/styles', $area);
            $stylesDir         = $this->projectDir . '/' . ltrim($relativeStylesDir, '/');
            if (!is_dir($stylesDir) && !mkdir($stylesDir, 0755, true) && !is_dir($stylesDir)) {
                $io->error(sprintf('Failed to create styles directory: %s', $stylesDir));
                continue;
            }

            $themeFile = $stylesDir . '/custom-theme.scss';
            if (file_exists($themeFile) && !$force) {
                $io->warning(sprintf('SCSS theme file already exists, skipping: %s', $themeFile));
            } else {
                $variables = $themeConfig['cssVariables'] ?? [];
                $lines     = [":root {"];
                foreach ($variables as $name => $value) {
                    $lines[] = sprintf('    %s: %s;', $name, $value);
                }
                $lines[] = '}';

                file_put_contents($themeFile, implode("\n", $lines) . "\n");
                $io->success(sprintf('Generated theme file: %s', $themeFile));
            }

            // Dopisanie importu w assets/<area>/entrypoint.js
            $entryFile = $this->projectDir . sprintf('/assets/%s/entrypoint.js', $area);
            if (file_exists($entryFile)) {
                $importLine = "import './styles/custom-theme.scss';";
                $content    = file_get_contents($entryFile);
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

            // 2b) Kopiowanie logo → assets/<area>/images/<logoFilename>
            if (empty($themeConfig['logo'])) {
                $io->text(sprintf('No logo defined for area "%s", skipping logo copy.', $area));
                continue;
            }

            $logoFilename = $themeConfig['logo'];
            $logoSrc      = sprintf('%s/store-creator/%s/themes/%s/%s',
                $this->projectDir,
                $store,
                $area,
                $logoFilename
            );
            if (!file_exists($logoSrc)) {
                $io->warning(sprintf('Logo file for area "%s" not found: %s', $area, $logoSrc));
                continue;
            }

            $manager = new ImageManager(new Driver());
            $image = $manager->read($logoSrc);
            $image->resize(height:  50);
            $image->save();


            $assetsImagesDir = $this->projectDir . sprintf('/assets/%s/images', $area);
            if (!is_dir($assetsImagesDir) && !mkdir($assetsImagesDir, 0755, true) && !is_dir($assetsImagesDir)) {
                $io->error(sprintf('Failed to create assets images directory: %s', $assetsImagesDir));
                continue;
            }

            $destLogoInAssets = $assetsImagesDir . '/' . $logoFilename;
            if (file_exists($destLogoInAssets) && !$force) {
                $io->warning(sprintf('Logo already exists in assets (won’t overwrite unless --force): %s', $destLogoInAssets));
            }

            copy($logoSrc, $destLogoInAssets);
            $io->success(sprintf('Copied logo for area "%s" to assets: %s', $area, $destLogoInAssets));
        }

        //
        // 3) Rebuild assets przez Webpack Encore (aby wyprodukować hashowane nazwy obrazów)
        //
        $io->section('Building assets with Webpack Encore');
        $process = Process::fromShellCommandline('yarn encore dev', $this->projectDir);
        $process->run(fn($type, $buffer) => $io->write($buffer));
        if (!$process->isSuccessful()) {
            $io->error('Asset build failed.');
            return Command::FAILURE;
        }
        $io->success('Assets built successfully.');

        //
        // 4) Po buildzie – odczytamy rzeczywistą nazwę zhaszowanego logo i wygenerujemy Twig w odpowiednim katalogu templates/<area>/
        //
        foreach ($themes as $area => $themeConfig) {
            if (empty($themeConfig['logo'])) {
                continue;
            }

            $logoFilename = $themeConfig['logo'];
            // Katalog z wytworzonymi przez Encore plikami obrazów:
            $publicImagesDir = sprintf('%s/public/build/app/%s/images', $this->projectDir, $area);
            if (!is_dir($publicImagesDir)) {
                $io->warning(sprintf('Po buildzie nie znaleziono katalogu z obrazami: %s', $publicImagesDir));
                continue;
            }

            // Wyciągamy bazową nazwę (bez rozszerzenia):
            $basename = pathinfo($logoFilename, PATHINFO_FILENAME);
            $extension = pathinfo($logoFilename, PATHINFO_EXTENSION);

            // Szukamy w katalogu pliku matching „basename.*.extension” (np. logo.abc123.png)
            $pattern = sprintf('%s/%s.*.%s', $publicImagesDir, $basename, $extension);
            $matches = glob($pattern);

            if (empty($matches)) {
                // Jeżeli wynik pusty, spróbujemy fallback do oryginalnej nazwy (bez hasha)
                $fallbackPath = sprintf('%s/%s', $publicImagesDir, $logoFilename);
                if (file_exists($fallbackPath)) {
                    $matches[] = $fallbackPath;
                }
            }

            if (empty($matches)) {
                $io->warning(sprintf('Nie znaleziono zhaszowanego pliku logo w: %s (pattern: %s)', $publicImagesDir, $pattern));
                continue;
            }

            // Zwykle glob zwróci tablicę, bierzemy pierwszy pasujący:
            $hashedFullPath = $matches[0];
            // Teraz konwertujemy na ścieżkę relatywną względem katalogu public/
            // public/build/<area>/images/logo.abc123.png
            $relativePublicPath = substr($hashedFullPath, strlen($this->projectDir . '/public/'));

            // Czyli: "build/<area>/images/logo.abc123.png"
            // Generujemy Twig‐a w templates/<area>/logo.html.twig
            $twigDir    = $this->projectDir . '/templates/' . $area;
            if (!is_dir($twigDir) && !mkdir($twigDir, 0755, true) && !is_dir($twigDir)) {
                $io->error(sprintf('Failed to create templates directory for area "%s": %s', $area, $twigDir));
                continue;
            }

            $twigFilename = 'logo.html.twig';
            $twigPath     = $twigDir . '/' . $twigFilename;

            // Właściwe odwołanie w Twig:
            $assetPath   = $relativePublicPath; // np. "build/shop/images/logo.abc123.png"
            $routeName   = ($area === 'shop') ? 'sylius_shop_homepage' : 'sylius_admin_dashboard';

            $twigContent = <<<TWIG
{# templates/{$area}/{$twigFilename} #}
<a href="{{ path('{$routeName}') }}" class="app-{$area}-logo">
    <img src="{{ asset('{$assetPath}') }}" alt="Logo {$area}" />
</a>
TWIG;

            if (file_exists($twigPath) && !$force) {
                $io->warning(sprintf('Twig template already exists, overwriting: %s', $twigPath));
            }

            file_put_contents($twigPath, $twigContent . "\n");
            $io->success(sprintf('Created Twig logo template for area "%s": %s', $area, $twigPath));
        }

        //
        // 5) Aktualizacja konfiguracji Sylius Twig Hooks
        //
        $io->section('Updating Twig hook configuration');
        $hooksConfigPath = $this->projectDir . '/config/packages/sylius_twig_hooks.yaml';

        if (!file_exists($hooksConfigPath)) {
            $baseConfig = <<<YAML
sylius_twig_hooks:
    hooks:
YAML;
            file_put_contents($hooksConfigPath, $baseConfig . "\n");
            $io->success(sprintf('Created new hook config: %s', $hooksConfigPath));
        }

        $hooksContent = file_get_contents($hooksConfigPath);

        foreach ($themes as $area => $themeConfig) {
            if (empty($themeConfig['logo'])) {
                continue;
            }

            if ($area === 'shop') {
                $hookKey      = 'sylius_shop.base.header.content.logo';
                $twigTemplate = sprintf('%s/logo.html.twig', $area); // shop/logo.html.twig
            } else {
                $hookKey      = 'sylius_admin.layout.header';
                $twigTemplate = sprintf('%s/logo.html.twig', $area); // admin/logo.html.twig
            }

            if (strpos($hooksContent, $hookKey) !== false) {
                $io->text(sprintf('Hook "%s" already present, skipping.', $hookKey));
                continue;
            }

            $snippet = <<<YAML

        '{$hookKey}':
            content:
                template: '{$twigTemplate}'
                priority: 0
YAML;
            $updated = preg_replace(
                '/(sylius_twig_hooks:\s*\n\s*hooks:\s*)/m',
                "\$1" . $snippet,
                $hooksContent,
                1
            );

            if ($updated === null) {
                $io->error('Failed to update hooks config (preg_replace returned null).');
            } else {
                file_put_contents($hooksConfigPath, $updated);
                $hooksContent = $updated;
                $io->success(sprintf('Appended hook "%s" to %s', $hookKey, $hooksConfigPath));
            }
        }

        return Command::SUCCESS;
    }
}
