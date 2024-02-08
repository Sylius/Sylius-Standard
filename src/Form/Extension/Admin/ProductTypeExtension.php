<?php

declare(strict_types=1);

namespace App\Form\Extension\Admin;

use Sylius\Bundle\ProductBundle\Form\Type\ProductType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductTypeExtension extends AbstractTypeExtension
{
    private const COLORS = [
        'red' => 'sylius.form.product.red',
        'blue' => 'sylius.form.product.blue',
        'green' => 'sylius.form.product.green',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('color', ChoiceType::class, [
                'label' => 'sylius.form.product.color',
                'choices' => array_flip(self::COLORS),
                'placeholder' => 'sylius.form.product.select_color',
                'required' => false,
            ])
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductType::class];
    }
}
