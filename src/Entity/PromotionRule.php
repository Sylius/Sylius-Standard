<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Promotion\Model\PromotionRule as BasePromotionRule;

/**
 * @MappedSuperclass
 */
class PromotionRule extends BasePromotionRule
{
}
