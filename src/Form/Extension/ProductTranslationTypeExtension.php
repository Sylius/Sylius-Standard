<?php

declare(strict_types=1);

namespace App\Form\Extension;

use App\Form\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Sylius\Bundle\ProductBundle\Form\Type\ProductTranslationType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductTranslationTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('color', ColorType::class, [
            'label' => 'sylius.form.product.color',
        ]);
    }

    public function getExtendedType(): string
    {
        return ProductTranslationType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
