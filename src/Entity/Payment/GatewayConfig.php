<?php

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Bundle\PayumBundle\Model\GatewayConfig as BaseGatewayConfig;

/**
 * @MappedSuperclass
 */
class GatewayConfig extends BaseGatewayConfig
{
}
