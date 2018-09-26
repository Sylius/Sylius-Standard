<?php

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\PaymentMethod as BasePaymentMethod;
use Sylius\Component\Payment\Model\PaymentMethodTranslationInterface;

/**
 * @MappedSuperclass
 */
class PaymentMethod extends BasePaymentMethod
{
    protected function createTranslation(): PaymentMethodTranslationInterface
    {
        return new PaymentMethodTranslation();
    }
}
