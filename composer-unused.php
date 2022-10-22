<?php

declare(strict_types=1);

use ComposerUnused\ComposerUnused\Configuration\Configuration;
use ComposerUnused\ComposerUnused\Configuration\PatternFilter;
use ComposerUnused\ComposerUnused\Configuration\NamedFilter;
use Webmozart\Glob\Glob;

return static function (Configuration $config): Configuration {
    return $config
        ->addNamedFilter(NamedFilter::fromString('symfony/flex'))
        ->setAdditionalFilesFor(
            'sylius/sylius-standard',
            [__DIR__ . '/config/bundles.php', __DIR__ . '/config/bootstrap.php']
        )
    ;
};
