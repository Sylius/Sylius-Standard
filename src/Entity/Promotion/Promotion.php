<?php

declare(strict_types=1);

namespace App\Entity\Promotion;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\Promotion as BasePromotion;

/**
 * @Entity
 * @Table(name="sylius_promotion")
 */
class Promotion extends BasePromotion
{
}
