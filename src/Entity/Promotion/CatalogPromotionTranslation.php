<?php

declare(strict_types=1);

namespace App\Entity\Promotion;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Promotion\Model\CatalogPromotionTranslation as BaseCatalogPromotionTranslation;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_catalog_promotion_translation')]
class CatalogPromotionTranslation extends BaseCatalogPromotionTranslation
{
}
