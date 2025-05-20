<?php
declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Style\SymfonyStyle;

trait PluginConfigTrait
{
    private const ENV_PLUGINS = 'SYLIUS_PLUGINS_JSON';

    /**
     * @return array<string,string>  [package => version]
     */
    private function loadPlugins(SymfonyStyle $io): array
    {
        $raw = getenv(self::ENV_PLUGINS) ?: '';
        if ('' === trim($raw)) {
            $io->error(sprintf('Env var %s is missing or empty.', self::ENV_PLUGINS));
            throw new \RuntimeException('Missing plugins JSON');
        }

        try {
            $plugins = json_decode($raw, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $io->error(sprintf('Env var %s contains invalid JSON: %s', self::ENV_PLUGINS, $e->getMessage()));
            throw new \RuntimeException('Invalid plugins JSON', 0, $e);
        }

        if (!is_array($plugins)) {
            $io->error(sprintf('Env var %s did not decode to an array.', self::ENV_PLUGINS));
            throw new \RuntimeException('Plugins JSON not an array');
        }

        return $plugins;
    }
}
