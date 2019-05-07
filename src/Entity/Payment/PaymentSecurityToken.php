<?php

declare(strict_types=1);

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Bundle\PayumBundle\Model\PaymentSecurityToken as BasePaymentSecurityToken;

/**
 * @Entity
 * @Table(name="sylius_payment_security_token")
 */
class PaymentSecurityToken extends BasePaymentSecurityToken
{
}
