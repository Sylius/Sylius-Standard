<?php
declare(strict_types=1);
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([__DIR__ . '/src']);

    foreach (glob(__DIR__ . '/vendor/*/*/composer.json') as $file) {
        $data = json_decode(file_get_contents($file), true);
        $sets = $data['extra']['sylius-plugin-installer']['rector-sets'] ?? [];
        foreach ($sets as $setReference) {
            [$class, $const] = explode('::', $setReference, 2);
            $rectorConfig->sets([constant($class . '::' . $const)]);
        }
    }

    $rectorConfig->importNames();
    $rectorConfig->removeUnusedImports();
};
