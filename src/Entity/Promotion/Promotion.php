<?php

namespace App\Entity\Promotion;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\Promotion as BasePromotion;

/**
 * @MappedSuperclass
 */
class Promotion extends BasePromotion
{
}
