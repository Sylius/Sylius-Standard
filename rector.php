<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Sylius\SyliusRector\Set\SyliusPlus;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/vendor/sylius/sylius-rector/config/config.php');
    $rectorConfig->paths([__DIR__ . '/src']);

    foreach (glob(__DIR__ . '/vendor/sylius/*/rector.php') as $pluginRectorConfig) {
        /** @var callable $configurePlugin */
        $configurePlugin = require $pluginRectorConfig;
        $configurePlugin($rectorConfig);
    }

    $rectorConfig->importNames();
    $rectorConfig->removeUnusedImports();
};
