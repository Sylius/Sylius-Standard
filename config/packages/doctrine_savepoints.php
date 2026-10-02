<?php

declare(strict_types=1);

use Composer\InstalledVersions;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    // doctrine/doctrine-bundle 3.x always uses savepoints for nested transactions
    // and no longer allows the option to be set.
    if (version_compare((string) InstalledVersions::getVersion('doctrine/doctrine-bundle'), '3.0.0', '>=')) {
        return;
    }

    $container->extension('doctrine', [
        'dbal' => [
            'use_savepoints' => true,
        ],
    ]);
};
