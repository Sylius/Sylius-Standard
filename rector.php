<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->importNames();
    $rectorConfig->removeUnusedImports();
    $rectorConfig->import(__DIR__ . '/vendor/sylius/sylius-rector/config/config.php');
    $rectorConfig->paths([
        __DIR__ . '/src'
    ]);
};
