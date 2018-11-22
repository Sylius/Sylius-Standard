<?php

declare(strict_types=1);

namespace App\Entity\Order;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Order\Model\Adjustment as BaseAdjustment;

/**
 * @MappedSuperclass
 * @Table(name="sylius_adjustment")
 */
class Adjustment extends BaseAdjustment
{
}
