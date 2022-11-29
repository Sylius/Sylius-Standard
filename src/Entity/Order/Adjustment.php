<?php

declare(strict_types=1);

namespace App\Entity\Order;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Adjustment as BaseAdjustment;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_adjustment")
 */
class Adjustment extends BaseAdjustment
{
}
