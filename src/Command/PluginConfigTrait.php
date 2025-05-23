<?php

declare(strict_types=1);

namespace App\Command;

use JsonException;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait PluginConfigTrait
{
    private const ENV_PLUGINS = 'SYLIUS_PLUGINS_JSON';
    private const FILE_NAME = 'sylius-plugins.json';

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
            }

            if (!empty($data) && is_array($data)) {
                return $data;
            }
            $io->warning(sprintf('File %s did not decode to an array, falling back to env-var.', self::FILE_NAME));
        }

        $plugins = $container->getParameter('sylius_plugins') ?? [];
        if (!is_array($plugins)) {
            $io->error(sprintf('Env var %s did not decode to an array.', self::ENV_PLUGINS));
            throw new \RuntimeException('Plugins JSON not an array');
        }

        $io->text(sprintf('Loaded plugin config from env var %s', self::ENV_PLUGINS));

        return $plugins;
    }

    private function getSupportedPlugins(): array
    {
        return [
            "sylius/b2b-kit" => "2.0.x-dev",
            "sylius/cms-plugin" => "1.0.x-dev",
            "sylius/customer-service-plugin" => "2.0.x-dev",
            "sylius/loyalty-plugin" => "2.0.x-dev",
            "sylius/return-plugin" => "2.0.x-dev",
            "sylius/invoicing-plugin" => "2.0.x-dev",
        ];
    }
}
