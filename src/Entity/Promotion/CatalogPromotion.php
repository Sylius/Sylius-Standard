<?php

declare(strict_types=1);

namespace App\Entity\Promotion;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\CatalogPromotion as BaseCatalogPromotion;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_catalog_promotion")
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_catalog_promotion')]
class CatalogPromotion extends BaseCatalogPromotion
{
}
