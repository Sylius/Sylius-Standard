<?php

declare(strict_types=1);

namespace App\Entity\Promotion;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\Promotion as BasePromotion;

/**
 * @MappedSuperclass
 * @Table(name="sylius_promotion")
 */
class Promotion extends BasePromotion
{
}
