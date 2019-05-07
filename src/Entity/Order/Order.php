<?php

declare(strict_types=1);

namespace App\Entity\Order;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\Order as BaseOrder;

/**
 * @Entity
 * @Table(name="sylius_order")
 */
class Order extends BaseOrder
{
}
