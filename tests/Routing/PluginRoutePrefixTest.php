<?php

declare(strict_types=1);

namespace App\Tests\Routing;

use App\Tests\Routing\Fixtures\PluginRoutePrefixKernel;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\RouterInterface;

final class PluginRoutePrefixTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return PluginRoutePrefixKernel::class;
    }

    #[Test]
    public function it_keeps_the_admin_prefix_of_plugin_routes_declared_with_attributes(): void
    {
        // Debug mode makes the router cache track config/routes.yaml changes.
        self::bootKernel(['debug' => true]);

        /** @var RouterInterface $router */
        $router = self::getContainer()->get('router');
        $route = $router->getRouteCollection()->get('app_test_plugin_admin_dashboard');

        self::assertNotNull($route);
        self::assertSame('/admin/plugin-dashboard', $route->getPath());
    }
}
