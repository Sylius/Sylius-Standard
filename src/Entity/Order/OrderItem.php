<?php

namespace App\Entity\Order;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\OrderItem as BaseOrderItem;

/**
 * @MappedSuperclass
 */
class OrderItem extends BaseOrderItem
{
}
