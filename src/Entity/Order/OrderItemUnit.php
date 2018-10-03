<?php

declare(strict_types=1);

namespace App\Entity\Order;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\OrderItemUnit as BaseOrderItemUnit;

/**
 * @MappedSuperclass
 * @Table(name="sylius_order_item_unit")
 */
class OrderItemUnit extends BaseOrderItemUnit
{
}
