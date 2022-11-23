<?php
declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Product\Product;
use App\Form\Type\Color\ColorFormType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExtendedProductForm extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('color', ColorFormType::class,[
            'label'=>'Kolor'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }

    public function getExtendedType(): string
    {
        return ProductType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductType::class];
    }
}
