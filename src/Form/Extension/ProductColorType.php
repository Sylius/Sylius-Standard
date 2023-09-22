<?php

namespace App\Form\Extension;

use App\Entity\Product\Product;
use Sylius\Bundle\ProductBundle\Form\Type\ProductType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductColorType extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('color', ChoiceType::class, [
            'label' => 'sylius.form.product.color',
            'choices' => [
                'sylius.form.product.color.red' => Product::COLOR_RED,
                'sylius.form.product.color.blue' => Product::COLOR_BLUE,
                'sylius.form.product.color.green' => Product::COLOR_GREEN,
            ],
            'required' => false,
        ]);
    }
    public static function getExtendedTypes(): iterable
    {
        return [ProductType::class];
    }
}
