<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\Shipment as BaseShipment;

/**
 * @MappedSuperclass
 */
class Shipment extends BaseShipment
{
}
