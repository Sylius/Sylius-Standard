<?php

namespace App\Entity\Order;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\OrderItemUnit as BaseOrderItemUnit;

/**
 * @MappedSuperclass
 */
class OrderItemUnit extends BaseOrderItemUnit
{
}
