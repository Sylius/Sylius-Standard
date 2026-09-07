<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\DependencyInjection;

use Override;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    #[Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        return new TreeBuilder('sylius_order_note');
    }
}
