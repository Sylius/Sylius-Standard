<?php

declare(strict_types=1);

namespace App\Command;

use RuntimeException;
use Exception;

trait ConfigTrait
{
    public function validateStore(?string $store = null): void
    {
        $configPath = sprintf('%s/store-preset/store-preset.json', $this->projectDir);

        if (!file_exists($configPath)) {
            throw new RuntimeException(sprintf('Configuration file not found: %s', $configPath));
        }

        if ($store === null) {
            return;
        }

        $config = json_decode(file_get_contents($configPath), true, 512, JSON_THROW_ON_ERROR);
        if ($config['name'] !== $store) {
            throw new RuntimeException(sprintf(
                'Store name "%s" does not match the expected name "%s" in %s',
                $store,
                $config['name'],
                $configPath
            ));
        }
    }

    public function getStoreName(): ?string
    {
        $configPath = sprintf('%s/store-preset/store-preset.json', $this->projectDir);

        if (!file_exists($configPath)) {
            return null;
        }

        $config = json_decode(file_get_contents($configPath), true, 512, JSON_THROW_ON_ERROR);

        return $config['name'] ?? throw new RuntimeException(sprintf('Store name not found in configuration: %s', $configPath));
    }

    public function getPluginsByStore(string $store): array
    {
        $configPath = sprintf('%s/store-preset/store-preset.json', $this->projectDir);
        if (!file_exists($configPath)) {

            throw new RuntimeException(sprintf('Configuration file not found: %s', $configPath));
        }

        try {
            $data = json_decode((string)file_get_contents($configPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            throw new RuntimeException(sprintf('Failed to parse JSON from %s: %s', $configPath, $e->getMessage()));
        }

        $plugins = $data['plugins'] ?? [];
        if (empty($plugins)) {
            throw new RuntimeException(sprintf('No plugins found in configuration: %s', $configPath));
        }

        return $plugins;
    }

    public function getFixturesPath(): string
    {
        $fixturesPath = sprintf('%s/store-preset/fixtures/fixtures.yaml', $this->projectDir);

        if (!file_exists($fixturesPath)) {
            throw new RuntimeException(sprintf('Fixtures file not found: %s', $fixturesPath));
        }

        return $fixturesPath;
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
