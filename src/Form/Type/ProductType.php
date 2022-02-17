<?php

namespace App\Form\Type;


use App\Entity\Product\Product;
use Sylius\Bundle\ProductBundle\Form\Type\ProductType as BaseProductType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'color',
                ChoiceType::class,
                [
                    'label' => 'color.label',
                    'choices' => [
                        Product::COLOR_BLUE,
                        Product::COLOR_GREEN,
                        Product::COLOR_RED,
                    ],
                    'choice_label' => function ($choice, $key, $value) {
                        return 'colors.'.strtolower($value);
                    },
                ]
            );
    }

    public function getParent(): string
    {
        return BaseProductType::class;
    }
}
