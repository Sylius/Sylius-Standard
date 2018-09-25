<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\PaymentMethod as BasePaymentMethod;

/**
 * @MappedSuperclass
 */
class PaymentMethod extends BasePaymentMethod
{
}
