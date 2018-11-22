<?php

declare(strict_types=1);

namespace App\Entity\Promotion;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Promotion\Model\PromotionAction as BasePromotionAction;

/**
 * @MappedSuperclass
 * @Table(name="sylius_promotion_action")
 */
class PromotionAction extends BasePromotionAction
{
}
