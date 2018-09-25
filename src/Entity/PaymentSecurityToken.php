<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Bundle\PayumBundle\Model\PaymentSecurityToken as BasePaymentSecurityToken;

/**
 * @MappedSuperclass
 */
class PaymentSecurityToken extends BasePaymentSecurityToken
{
}
