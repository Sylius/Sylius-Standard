<?php

declare(strict_types=1);

namespace App\Fixtures;

use App\Entity\Product\ProductAttribute;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Bundle\FixturesBundle\Fixture\FixtureInterface;

class ProductFixtures extends AbstractFixture implements FixtureInterface
{
    private ObjectManager $manager;
    private ProductFactoryInterface $productFactory;

    public function __construct(
        ObjectManager $manager,
        ProductFactoryInterface $productFactory
    ) {
        $this->manager = $manager;
        $this->productFactory = $productFactory;
    }

    public function getName(): string
    {
        return 'product';
    }

    public function load(array $options): void
    {
        $this->createProduct();

        $this->manager->flush();
    }

    private function createProduct(): void
    {
        $product = ($this->productFactory->createNew())
            ->setName('T-Shirt')
            ->setSlug('t-shirt')
            ->setCode('00001');

        if ($attribute = $this->getColorAttribute()) {
            $product->addAttribute($attribute);
        }

        $this->manager->persist($product);
    }

    private function getColorAttribute(): ?AttributeValueInterface
    {
        return $this->manager->getRepository(ProductAttribute::class)
            ->findOneBy(['code' => 'product_color']);
    }
}
