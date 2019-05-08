<?php

declare(strict_types=1);

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Payment\Model\PaymentMethodTranslation as BasePaymentMethodTranslation;

/**
 * @Entity
 * @Table(name="sylius_payment_method_translation")
 */
class PaymentMethodTranslation extends BasePaymentMethodTranslation
{
}
