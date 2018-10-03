<?php

declare(strict_types=1);

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Payment\Model\PaymentMethodTranslation as BasePaymentMethodTranslation;

/**
 * @MappedSuperclass
 * @Table(name="sylius_payment_method_translation")
 */
class PaymentMethodTranslation extends BasePaymentMethodTranslation
{
}
