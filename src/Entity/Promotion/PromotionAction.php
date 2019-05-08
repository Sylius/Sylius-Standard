<?php

declare(strict_types=1);

namespace App\Entity\Promotion;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Promotion\Model\PromotionAction as BasePromotionAction;

/**
 * @Entity
 * @Table(name="sylius_promotion_action")
 */
class PromotionAction extends BasePromotionAction
{
}
