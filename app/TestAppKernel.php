<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

require_once __DIR__.'/AppKernel.php';

use PSS\SymfonyMockerContainer\DependencyInjection\MockerContainer;

class TestAppKernel extends AppKernel
{
    public function registerBundles(): array
    {
        return array_merge(
            parent::registerBundles(),
            [new \Sylius\Behat\Application\SyliusTestPlugin\SyliusTestPlugin()]
        );
    }

    protected function getContainerBaseClass(): string
    {
        return MockerContainer::class;
    }
}
