<?php

declare(strict_types=1);

namespace App\Entity\Order;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Order\Model\Adjustment as BaseAdjustment;

/**
 * @Entity
 * @Table(name="sylius_adjustment")
 */
class Adjustment extends BaseAdjustment
{
}
