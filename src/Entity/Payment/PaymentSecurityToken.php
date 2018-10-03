<?php

declare(strict_types=1);

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Bundle\PayumBundle\Model\PaymentSecurityToken as BasePaymentSecurityToken;

/**
 * @MappedSuperclass
 * @Table(name="sylius_payment_security_token")
 */
class PaymentSecurityToken extends BasePaymentSecurityToken
{
}
