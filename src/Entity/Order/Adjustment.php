<?php

namespace App\Entity\Order;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Order\Model\Adjustment as BaseAdjustment;

/**
 * @MappedSuperclass
 */
class Adjustment extends BaseAdjustment
{
}
