<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\OrderItemUnit as BaseOrderItemUnit;

/**
 * @MappedSuperclass
 */
class OrderItemUnit extends BaseOrderItemUnit
{
}
