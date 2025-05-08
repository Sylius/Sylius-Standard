<?php

declare(strict_types=1);

namespace App\Repository;

use Sylius\Bundle\CoreBundle\Doctrine\ORM\ShipmentRepository as BaseShipmentRepository;
use Sylius\MarketplaceSuite\Component\Order\Repository\ShipmentRepositoryInterface;
use Sylius\MarketplaceSuite\Component\Order\Repository\ShipmentRepositoryTrait;

class ShipmentRepository extends BaseShipmentRepository implements ShipmentRepositoryInterface
{
    use ShipmentRepositoryTrait;
}
