<?php

declare(strict_types=1);

namespace App\Tests\Routing\Fixtures;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Stands for a plugin controller declaring its routes with #[Route],
 * meant to be imported by the application under the admin prefix.
 */
final class AdminPluginController
{
    #[Route('/plugin-dashboard', name: 'app_test_plugin_admin_dashboard')]
    public function __invoke(): Response
    {
        return new Response();
    }
}
