<?php

declare(strict_types=1);

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\Payment as BasePayment;

/**
 * @MappedSuperclass
 * @Table(name="sylius_payment")
 */
class Payment extends BasePayment
{
}
