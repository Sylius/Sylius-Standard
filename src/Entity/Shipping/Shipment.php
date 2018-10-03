<?php

declare(strict_types=1);

namespace App\Entity\Shipping;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\Shipment as BaseShipment;

/**
 * @MappedSuperclass
 * @Table(name="sylius_shipment")
 */
class Shipment extends BaseShipment
{
}
