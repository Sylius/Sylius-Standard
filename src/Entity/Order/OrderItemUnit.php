<?php

declare(strict_types=1);

namespace App\Entity\Order;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\OrderItemUnit as BaseOrderItemUnit;

/**
 * @Entity
 * @Table(name="sylius_order_item_unit")
 */
class OrderItemUnit extends BaseOrderItemUnit
{
}
