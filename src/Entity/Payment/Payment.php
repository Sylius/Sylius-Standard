<?php

declare(strict_types=1);

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\Payment as BasePayment;

/**
 * @Entity
 * @Table(name="sylius_payment")
 */
class Payment extends BasePayment
{
}
