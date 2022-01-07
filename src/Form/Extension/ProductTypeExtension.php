<?php

declare(strict_types=1);

namespace App\Form\Extension;

use App\Form\Type\ProductColorType;
use Sylius\Bundle\AdminApiBundle\Form\Type\ProductType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('color', EnumType::class, [
            'label' => ProductColorType::class,
            'required' => true,
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductType::class];
    }
}
