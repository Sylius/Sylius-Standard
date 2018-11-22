<?php

declare(strict_types=1);

namespace App\Entity\Promotion;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\PromotionCoupon as BasePromotionCoupon;

/**
 * @MappedSuperclass
 * @Table(name="sylius_promotion_coupon")
 */
class PromotionCoupon extends BasePromotionCoupon
{
}
