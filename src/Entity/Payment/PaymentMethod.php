<?php

declare(strict_types=1);

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\PaymentMethod as BasePaymentMethod;
use Sylius\Component\Payment\Model\PaymentMethodTranslationInterface;

/**
 * @MappedSuperclass
 * @Table(name="sylius_payment_method")
 */
class PaymentMethod extends BasePaymentMethod
{
    protected function createTranslation(): PaymentMethodTranslationInterface
    {
        return new PaymentMethodTranslation();
    }
}
