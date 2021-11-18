<?php

namespace App\Form\Extension;

use Sylius\Bundle\ProductBundle\Form\Type\ProductType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductTypeExtension extends AbstractTypeExtension {
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('color', ChoiceType::class, [
            'required' => false,
            'multiple' => false,
            'label' => 'Color',
            'choices'  => [
                'Red' => 'Red',
                'Blue' => 'Red',
                'Green' => 'Red',
            ],
        ]);
    }


    public static function getExtendedTypes(): iterable
    {
        return [ProductType::class];
    }
}
