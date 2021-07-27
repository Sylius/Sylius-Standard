<?php

declare(strict_types=1);

namespace App\Form\Type\Product;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ColorType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'choices' => [
                    'sylius.product.color.none' => null,
                    'sylius.product.color.red' => 'red',
                    'sylius.product.color.blue' => 'blue',
                    'sylius.product.color.green' => 'green',
                ],
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'sylius_product_color';
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
