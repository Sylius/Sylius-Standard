<?php

declare(strict_types=1);

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Bundle\PayumBundle\Model\GatewayConfig as BaseGatewayConfig;

/**
 * @Entity
 * @Table(name="sylius_gateway_config")
 */
class GatewayConfig extends BaseGatewayConfig
{
}
