<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\DependencyInjection;

use Override;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

final class SyliusOrderNoteExtension extends Extension implements PrependExtensionInterface
{
    #[Override]
    public function load(
        array $configs,
        ContainerBuilder $container,
    ): void {
        $this->processConfiguration(new Configuration(), $configs);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');
    }

    #[Override]
    public function prepend(ContainerBuilder $container): void
    {
        foreach (['sylius_resource', 'twig_hooks'] as $file) {
            $path = __DIR__ . '/../../config/packages/' . $file . '.yaml';
            $container->addResource(new FileResource($path));
            $config = Yaml::parseFile($path);
            Assert::isArray($config);
            foreach ($config as $extension => $values) {
                Assert::string($extension);
                Assert::isArray($values);
                Assert::allString(array_keys($values));
                $container->prependExtensionConfig($extension, $values);
            }
        }

        $container->prependExtensionConfig(
            'doctrine',
            [
            'orm' => [
                'mappings' => [
                    'SyliusOrderNotePlugin' => [
                        'type' => 'xml',
                        'is_bundle' => false,
                        'dir' => __DIR__ . '/../../config/doctrine',
                        'prefix' => 'SyliusOrderNotePlugin\\Entity',
                    ],
                ],
            ],
            ],
        );
    }

    #[Override]
    public function getAlias(): string
    {
        return 'sylius_order_note';
    }
}
