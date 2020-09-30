<?php
declare(strict_types=1);

namespace App\Form\TypeExtension\Product;

use App\Entity\Product\Product;
use Sylius\Bundle\ProductBundle\Form\Type\ProductType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('color', ChoiceType::class, [
            'choices' => [
                Product::COLOR_RED,
                Product::COLOR_GREEN,
                Product::COLOR_BLUE,
            ],
            'required' => false,
            'translation_domain' => 'messages',
            'choice_label' => function ($choice) {
                return sprintf('app.form.product.color_variations.%s', $choice);
            }
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductType::class];
    }
}
