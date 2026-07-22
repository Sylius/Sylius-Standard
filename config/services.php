<?php

declare(strict_types=1);

use Sylius\Bundle\ResourceBundle\Controller\ResourceController;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/*
 * This file is the entry point to configure your own services.
 * Files in the packages/ subdirectory configure your dependencies.
 * See also https://symfony.com/doc/current/service_container/import.html
 */
return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // Default configuration for services in *this* file
    $services->defaults()
        // Automatically injects dependencies in your services
        ->autowire()
        // Automatically registers your services as commands, event subscribers, etc.
        ->autoconfigure()
        // Allows optimizing the container by removing unused services; this also means
        // fetching services directly from the container via $container->get() won't work
        ->private()
    ;

    $services->instanceof(ResourceController::class)->autowire(false);
    $services->instanceof(AbstractResourceType::class)->autowire(false);

    // Makes classes in src/ available to be used as services;
    // this creates a service per class whose id is the fully-qualified class name
    $services->load('App\\', '../src/')
        ->exclude([
            '../src/Entity/',
            '../src/Kernel.php',
        ])
    ;

    // Controllers are imported separately to make sure services can be injected
    // as action arguments even if you don't extend any base controller class
    $services->load('App\\Controller\\', '../src/Controller/')
        ->tag('controller.service_arguments')
    ;
};
