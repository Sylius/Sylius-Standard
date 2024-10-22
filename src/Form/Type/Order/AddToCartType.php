<?php
declare(strict_types=1);

namespace App\Form\Type\Order;

use Sylius\Bundle\CoreBundle\Form\Type\Order\AddToCartType as SyliusAddToCartType;
use Sylius\Bundle\OrderBundle\Form\Type\CartItemType;
use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AddToCartType extends AbstractResourceType
{
    public function __construct(
        private readonly SyliusAddToCartType $decoratedService
    ) {
        parent::__construct($this->decoratedService->dataClass, $this->decoratedService->validationGroups);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('cartItem', CartItemType::class, [
            'product' => $options['product'],
            'step_multiplier' => 10,
            'step_min' => 10
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $this->decoratedService->configureOptions($resolver);
    }

    public function getBlockPrefix(): string
    {
        return $this->decoratedService->getBlockPrefix();
    }
}
