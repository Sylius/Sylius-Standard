<?php

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\Payment as BasePayment;

/**
 * @MappedSuperclass
 */
class Payment extends BasePayment
{
}
