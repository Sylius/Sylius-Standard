<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->import('../vendor/sylius/sylius/src/Sylius/Behat/Resources/config/services.xml');

    $container->extension('sylius_api', [
        'enabled' => true,
    ]);
};
