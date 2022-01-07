<?php

declare(strict_types=1);

namespace App\Form\Extension;

use App\Form\Type\ProductColorType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('color', TextType::class, [
            'label' => 'app.form.extension.type.product.color',
            'required' => true,
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductColorType::class];
    }
}
