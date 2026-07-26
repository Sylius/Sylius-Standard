<?php

declare(strict_types=1);

namespace App\Fixture;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Pins an exact per-channel price on an already-created product.
 *
 * Sylius's own `product` fixture exposes no price option: ProductExampleFactory hardcodes
 * `$faker->numberBetween(100, 10000)` and its faker is unseeded, so every fixture load
 * produces different prices. Declaring this fixture *after* `product` in a suite makes
 * prices deterministic, which is what allows a pricing scenario to assert concrete values
 * instead of only relative ones.
 */
final class ChannelPricingFixture extends AbstractFixture
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getName(): string
    {
        return 'channel_pricing';
    }

    protected function configureOptionsNode(ArrayNodeDefinition $optionsNode): void
    {
        $optionsNode
            ->children()
                ->arrayNode('prices')
                    ->requiresAtLeastOneElement()
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('product')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('channel')->isRequired()->cannotBeEmpty()->end()
                            ->integerNode('price')->isRequired()->min(0)->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    public function load(array $options): void
    {
        foreach ($options['prices'] as $specification) {
            $product = $this->productRepository->findOneByCode($specification['product']);

            if (null === $product) {
                throw new InvalidArgumentException(sprintf(
                    'No product with code "%s" to price. Declare its `product` fixture before this one.',
                    $specification['product'],
                ));
            }

            $priced = false;

            /** @var ProductVariantInterface $variant */
            foreach ($product->getVariants() as $variant) {
                /** @var ChannelPricingInterface $channelPricing */
                foreach ($variant->getChannelPricings() as $channelPricing) {
                    if ($channelPricing->getChannelCode() === $specification['channel']) {
                        $channelPricing->setPrice($specification['price']);
                        $priced = true;
                    }
                }
            }

            if (!$priced) {
                throw new InvalidArgumentException(sprintf(
                    'Product "%s" has no pricing for channel "%s".',
                    $specification['product'],
                    $specification['channel'],
                ));
            }
        }

        $this->entityManager->flush();
    }
}
