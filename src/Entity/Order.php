<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\Order as BaseOrder;

/**
 * @MappedSuperclass
 */
class Order extends BaseOrder
{
}
