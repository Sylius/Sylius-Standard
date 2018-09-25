<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\Payment as BasePayment;

/**
 * @MappedSuperclass
 */
class Payment extends BasePayment
{
}
