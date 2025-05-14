<?php

declare(strict_types=1);

namespace App\Entity\Promotion;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\CatalogPromotion as BaseCatalogPromotion;
use Sylius\Component\Promotion\Model\CatalogPromotionTranslationInterface;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_catalog_promotion')]
class CatalogPromotion extends BaseCatalogPromotion
{
    protected function createTranslation(): CatalogPromotionTranslationInterface
    {
        return new CatalogPromotionTranslation();
    }
}
