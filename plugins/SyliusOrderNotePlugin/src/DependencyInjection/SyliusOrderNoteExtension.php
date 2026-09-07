<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class SyliusOrderNoteExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.xml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        if ($container->hasExtension('sylius_twig_hooks')) {
            $container->prependExtensionConfig('sylius_twig_hooks', [
                'hooks' => [
                    'sylius_admin.order.show.content.sections#right' => [
                        'note' => [
                            'template' => '@SyliusOrderNotePlugin/admin/order/show/content/sections/note.html.twig',
                            'priority' => 50,
                        ],
                    ],
                ],
            ]);
        }
    }

    public function getAlias(): string
    {
        return 'sylius_order_note';
    }
}
