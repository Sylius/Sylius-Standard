<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace App\Fixtures;

use App\Entity\Product\ProductAttribute;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Bundle\FixturesBundle\Fixture\FixtureInterface;

class ProductAttributeFixtures extends AbstractFixture implements FixtureInterface
{
    private ObjectManager $manager;
    private AttributeFactoryInterface $attributeFactory;
    private FactoryInterface $attributeValueFactory;

    public function __construct(
        ObjectManager $manager,
        AttributeFactoryInterface $attributeFactory,
        FactoryInterface $attributeValueFactory
    ) {
        $this->manager = $manager;
        $this->attributeFactory = $attributeFactory;
        $this->attributeValueFactory = $attributeValueFactory;
    }

    public function getName(): string
    {
        return 'product_attribute';
    }

    public function load(array $options): void
    {
        $this->createAttributeValues(
            $this->createAttribute()
        );

        $this->manager->flush();
    }

    private function createAttribute(): AttributeInterface
    {
        $attribute = ($this->attributeFactory->createTyped('select'))
            ->setName(ProductAttribute::COLOR['name'])
            ->setCode(ProductAttribute::COLOR['code']);
        $this->manager->persist($attribute);

        return $attribute;
    }

    private function createAttributeValues(AttributeInterface $attribute): void
    {
        foreach (ProductAttribute::COLOR['values'] as $value) {
            $color = ($this->attributeValueFactory->createNew())
                ->setAttribute($attribute)
                ->setValue($value);
            $this->manager->persist($color);
        }
    }
}
