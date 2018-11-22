<?php

declare(strict_types=1);

namespace App\Entity\Order;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\OrderItem as BaseOrderItem;

/**
 * @MappedSuperclass
 * @Table(name="sylius_order_item")
 */
class OrderItem extends BaseOrderItem
{
}
