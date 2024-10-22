<?php
declare(strict_types=1);

namespace App\Form\Type\Extension;

use Sylius\Bundle\OrderBundle\Form\Type\CartItemType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CartItemTypeQuantityExtension extends AbstractTypeExtension
{
    public function __construct(
        private DataMapperInterface $dataMapper
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['step_multiplier', 'step_min']);
        $resolver->setDefault('step_multiplier', 1);
        $resolver->setDefault('step_min', 1);
        $resolver->setAllowedTypes('step_multiplier', 'int');
        $resolver->setAllowedTypes('step_min', 'int');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->remove('quantity');
        $builder
            ->add('quantity', IntegerType::class, [
                'attr' => ['min' => $options['step_min'], 'step' => $options['step_multiplier']],
                'label' => 'sylius.ui.quantity',
            ])
            ->setDataMapper($this->dataMapper)
        ;
    }

    public static function getExtendedTypes(): iterable
    {
        return [CartItemType::class];
    }
}
