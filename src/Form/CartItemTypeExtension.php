<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use \Sylius\Bundle\CoreBundle\Form\Extension\CartItemTypeExtension as SyliusCartItemTypeExtension;

final class CartItemTypeExtension extends AbstractTypeExtension
{
    public function __construct(
        private SyliusCartItemTypeExtension $decorated,
        private int                         $quantityMinimum,
        private int                         $quantityStep,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->decorated->buildForm($builder, $options);

        $builder->add('quantity', IntegerType::class, [
            'attr'  => [
                'min'  => $this->quantityMinimum,
                'step' => $this->quantityStep,
            ],
            'label' => 'sylius.ui.quantity',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $this->decorated->configureOptions($resolver);
    }

    public static function getExtendedTypes(): iterable
    {
        return SyliusCartItemTypeExtension::getExtendedTypes();
    }
}
