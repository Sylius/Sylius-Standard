<?php

declare(strict_types=1);

namespace App\Fixture\Factory;

use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Attribute\AttributeType\SelectAttributeType;
use Sylius\Component\Attribute\Factory\AttributeFactoryInterface;
use Sylius\Component\Grid\Filter\ExistsFilter;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class ProductAttributeColorFactory extends AbstractFixture
{
    public function __construct(
        private AttributeFactoryInterface $attributeFactory,
        private RepositoryInterface $productAttributeRepository
    ) {
    }

    public function getName(): string
    {
        return 'colorSelect';
    }

    public function load(array $options): void
    {
        $colorAttribute = $this->attributeFactory->createTyped(SelectAttributeType::TYPE);

        $colorAttribute->setName('Kolor');
        $colorAttribute->setCode('color');
        $colorAttribute->setConfiguration([
            'choices' => [
                'red' => [
                    'en_US' => 'Red',
                    'pl_PL' => 'Czerwony'
                ],
                'blue' => [
                    'en_US' => 'Blue',
                    'pl_PL' => 'Niebieski'
                ],
                'green' => [
                    'en_US' => 'Green',
                    'pl_PL' => 'Zielony'
                ],
            ],
            'multiple' => false,
            'min' => null,
            'max' => null,
        ]);

        $this->productAttributeRepository->add($colorAttribute);
    }
}
