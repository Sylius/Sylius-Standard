<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Order\Model\Adjustment as BaseAdjustment;

/**
 * @MappedSuperclass
 */
class Adjustment extends BaseAdjustment
{
}
