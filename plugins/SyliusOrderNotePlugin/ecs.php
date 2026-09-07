<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $config): void {
    putenv('ALLOW_BITBAG_OS_HEADER=0');
    $config->import('vendor/bitbag/coding-standard/ecs.php');
    $config->paths([__DIR__ . '/src', __DIR__ . '/tests']);
};
