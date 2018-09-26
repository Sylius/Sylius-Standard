<?php

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Bundle\PayumBundle\Model\PaymentSecurityToken as BasePaymentSecurityToken;

/**
 * @MappedSuperclass
 */
class PaymentSecurityToken extends BasePaymentSecurityToken
{
}
