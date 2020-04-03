<?php

declare(strict_types=1);

namespace App\Form\Extension;

use App\Entity\Product\ProductColor;
use Sylius\Bundle\ProductBundle\Form\Type\ProductType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductColorExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $colors = array_combine(ProductColor::getColors(), ProductColor::getColors());

        $translatedColors = array_map(function(string $color) {
            return sprintf('sylius.form.product.color.%s', $color);
        }, $colors);

        $builder
            ->add('color', ChoiceType::class, [
                'choices' => array_flip($translatedColors),
                'label' => 'sylius.form.product.color',
            ])
        ;

        $builder->get('color')
            ->addModelTransformer(new CallbackTransformer(
                function (?ProductColor $color) {
                    if($color === null) {
                        return;
                    }
                    return $color->getColor();
                },
                function (?string $color) {
                    if($color === null) {
                        return;
                    }
                    return new ProductColor($color);
                }
            ))
        ;
    }

    public function getExtendedTypes()
    {
        return [ProductType::class];
    }
}
