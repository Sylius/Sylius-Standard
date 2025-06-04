<?php

declare(strict_types=1);

namespace App\Command\Obsolete;

use JsonException;
use Symfony\Component\Console\Style\SymfonyStyle;

trait PluginConfigTrait
{
    private const ENV_PLUGINS = 'SYLIUS_PLUGINS_JSON';
    private const FILE_NAME = 'booster.json';

    /**
     * @return array<string,string>  [package => version]
     */
    private function loadPlugins(SymfonyStyle $io): array
    {
        /* @var ContainerInterface $container */
        $container = $this->getApplication()->getKernel()->getContainer();
        $projectDir = $container->getParameter('kernel.project_dir');
        $filePath = $projectDir . DIRECTORY_SEPARATOR . self::FILE_NAME;

        if (file_exists($filePath)) {
            $io->text(sprintf('Loading plugin config from "%s"', $filePath));
            $raw = file_get_contents($filePath);
            try {
                $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                $io->warning(sprintf('Invalid JSON in %s: %s', self::FILE_NAME, $e->getMessage()));
                return [];
            }

            if (is_array($data) && !empty($data)) {
                return $data['plugins'] ?? [];
            }

            $io->warning(sprintf('File %s did not decode to a non-empty array, falling back to none.', self::FILE_NAME));
        }

        return [];
    }

    /**
     * @return array<string,string>
     */
    private function loadThemes(SymfonyStyle $io): array
    {
        /* @var ContainerInterface $container */
        $container = $this->getApplication()->getKernel()->getContainer();
        $projectDir = $container->getParameter('kernel.project_dir');
        $filePath = $projectDir . DIRECTORY_SEPARATOR . self::FILE_NAME;

        if (file_exists($filePath)) {
            $io->text(sprintf('Loading plugin config from "%s"', $filePath));
            $raw = file_get_contents($filePath);
            try {
                $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                $io->warning(sprintf('Invalid JSON in %s: %s', self::FILE_NAME, $e->getMessage()));
                return [];
            }

            if (is_array($data) && !empty($data)) {
                return $data['themes'] ?? [];
            }

            $io->warning(sprintf('File %s did not decode to a non-empty array, falling back to none.', self::FILE_NAME));
        }

        return [];
    }

    /** @return array<string,string>  [package => version] */
    private function getSupportedPlugins(): array
    {
        return [
            "sylius/b2b-kit" => "2.0.x-dev",
            "sylius/cms-plugin" => "1.0.x-dev",
            "sylius/customer-service-plugin" => "2.0.x-dev",
            "sylius/loyalty-plugin" => "2.0.x-dev",
            "sylius/return-plugin" => "2.0.x-dev",
            "sylius/invoicing-plugin" => "2.0.x-dev",
            "sylius/paypal-plugin" => "2.0.x-dev",
        ];
    }

    /** @return array<string,string>  [package => version] */
    private function getInstalledPlugins(): array
    {
        /* @var ContainerInterface $container */
        $container = $this->getApplication()->getKernel()->getContainer();
        $projectDir = $container->getParameter('kernel.project_dir');
        $lockFile = $projectDir . '/composer.lock';

        if (!file_exists($lockFile)) {
            return [];
        }

        $raw = file_get_contents($lockFile);
        if (false === $raw) {
            return [];
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            // nieprawidłowy JSON w composer.lock
            return [];
        }

        $packages = $data['packages'] ?? [];
        if (isset($data['packages-dev']) && is_array($data['packages-dev'])) {
            $packages = array_merge($packages, $data['packages-dev']);
        }

        $installed = [];
        foreach ($packages as $package) {
            if (!isset($package['name'])) {
                continue;
            }
            $name = $package['name'];
            $version = $package['version'] ?? null;
            if (str_starts_with($name, 'sylius/') &&
                preg_match('/-(plugin|kit|suite)$/', $name)) {
                $installed[$name] = $version;
            }
        }

        return $installed;
    }
}
