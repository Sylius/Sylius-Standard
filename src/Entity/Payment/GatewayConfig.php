<?php

declare(strict_types=1);

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Bundle\PayumBundle\Model\GatewayConfig as BaseGatewayConfig;

/**
 * @MappedSuperclass
 * @Table(name="sylius_gateway_config")
 */
class GatewayConfig extends BaseGatewayConfig
{
}
