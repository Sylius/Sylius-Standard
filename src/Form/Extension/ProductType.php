<?php


namespace App\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductType extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('color', ChoiceType::class, [
                'required' => false,
                'choices'=>['red'=>'red','blue'=>'blue', 'green'=>'green'],
                'label' => 'Color',
            ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [\Sylius\Bundle\ProductBundle\Form\Type\ProductType::class];
    }
}
