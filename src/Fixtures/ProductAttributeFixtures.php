<?php

declare(strict_types=1);

namespace App\Fixtures;

use App\Entity\Product\ProductAttribute;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Bundle\FixturesBundle\Fixture\FixtureInterface;

class ProductAttributeFixtures extends AbstractFixture implements FixtureInterface
{
    private const ATTRIBUTE_NAME = 'Product color';
    private const ATTRIBUTE_CODE = 'product_color';
    private const ATTRIBUTE_VALUES = [
        ProductAttribute::COLOR_RED,
        ProductAttribute::COLOR_BLUE,
        ProductAttribute::COLOR_GREEN,
    ];

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
            ->setName(self::ATTRIBUTE_NAME)
            ->setCode(self::ATTRIBUTE_CODE);
        $this->manager->persist($attribute);

        return $attribute;
    }

    private function createAttributeValues(AttributeInterface $attribute): void
    {
        foreach (self::ATTRIBUTE_VALUES as $value) {
            $color = ($this->attributeValueFactory->createNew())
                ->setAttribute($attribute)
                ->setValue($value);
            $this->manager->persist($color);
        }
    }
}
