<?php

declare(strict_types=1);

use Composer\InstalledVersions;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    // Options that no longer exist in doctrine/doctrine-bundle 3.x, kept for doctrine-bundle 2.x:
    // - 3.x always uses savepoints for nested transactions,
    // - 3.x no longer generates proxy classes; Sylius removed `auto_generate_proxy_classes` from its
    //   configuration and asks applications still on doctrine-bundle 2.x to set it (see Sylius UPGRADE-2.3.md).
    if (version_compare((string) InstalledVersions::getVersion('doctrine/doctrine-bundle'), '3.0.0', '>=')) {
        return;
    }

    $container->extension('doctrine', [
        'dbal' => [
            'use_savepoints' => true,
        ],
        'orm' => [
            'auto_generate_proxy_classes' => !\in_array($container->env(), ['prod', 'test_cached'], true),
        ],
    ]);
};
