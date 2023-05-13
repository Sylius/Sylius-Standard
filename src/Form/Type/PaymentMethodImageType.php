<?php

declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\Payment\PaymentMethodImage;
use Sylius\Bundle\CoreBundle\Form\Type\ImageType;
use Symfony\Component\Form\FormBuilderInterface;

final class PaymentMethodImageType extends ImageType
{
    public function __construct()
    {
        parent::__construct(PaymentMethodImage::class, ['sylius']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder->remove('type');
    }

    public function getBlockPrefix(): string
    {
        return 'payment_method_image';
    }
}
