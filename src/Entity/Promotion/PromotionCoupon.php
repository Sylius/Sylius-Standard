<?php

namespace App\Entity\Promotion;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\PromotionCoupon as BasePromotionCoupon;

/**
 * @MappedSuperclass
 */
class PromotionCoupon extends BasePromotionCoupon
{
}
