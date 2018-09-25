<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Payment\Model\PaymentMethodTranslation as BasePaymentMethodTranslation;

/**
 * @MappedSuperclass
 */
class PaymentMethodTranslation extends BasePaymentMethodTranslation
{
}
