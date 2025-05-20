<?php
declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait PluginConfigTrait
{
    private const ENV_PLUGINS = 'SYLIUS_PLUGINS_JSON';

    /**
     * @return array<string,string>  [package => version]
     */
    private function loadPlugins(SymfonyStyle $io): array
    {
        /* @var ContainerInterface $container */
        $container = $this->getApplication()->getKernel()->getContainer();
        $plugins = $container->getParameter('sylius_plugins') ?? '';

        if (!is_array($plugins)) {
            $io->error(sprintf('Env var %s did not decode to an array.', self::ENV_PLUGINS));
            throw new \RuntimeException('Plugins JSON not an array');
        }

        return $plugins;
    }
}
