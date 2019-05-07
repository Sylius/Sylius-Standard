<?php

declare(strict_types=1);

namespace App\Entity\Order;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\OrderSequence as BaseOrderSequence;

/**
 * @Entity
 * @Table(name="sylius_order_sequence")
 */
class OrderSequence extends BaseOrderSequence
{
}
