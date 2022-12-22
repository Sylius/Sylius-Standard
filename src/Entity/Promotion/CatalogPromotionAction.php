<?php

declare(strict_types=1);

namespace App\Entity\Promotion;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Promotion\Model\CatalogPromotionAction as BaseCatalogPromotionAction;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_catalog_promotion_action")
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_catalog_promotion_action')]
class CatalogPromotionAction extends BaseCatalogPromotionAction
{
}
