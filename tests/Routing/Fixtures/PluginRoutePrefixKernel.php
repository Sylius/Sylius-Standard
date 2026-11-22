<?php

declare(strict_types=1);

namespace App\Tests\Routing\Fixtures;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Minimal kernel loading the application's config/routes.yaml after a plugin
 * route import prefixed with /admin, in the same order as App\Kernel does.
 */
final class PluginRoutePrefixKernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__, 3);
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/sylius_standard_plugin_route_prefix_kernel/cache';
    }

    public function getBuildDir(): string
    {
        return $this->getCacheDir();
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/sylius_standard_plugin_route_prefix_kernel/log';
    }

    private function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'secret' => 'test',
            'test' => true,
            'property_info' => ['enabled' => false],
        ]);

        $container->services()
            ->defaults()
                ->autowire()
                ->autoconfigure()
            ->set(AdminPluginController::class)
        ;
    }

    private function configureRoutes(RoutingConfigurator $routes): void
    {
        // Plugin routes are imported from config/routes/*.yaml, before config/routes.yaml.
        $routes->import(AdminPluginController::class, 'attribute')->prefix('/admin');
        $routes->import($this->getConfigDir() . '/routes.yaml');
    }
}
