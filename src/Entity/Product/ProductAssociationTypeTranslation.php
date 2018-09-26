<?php

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Product\Model\ProductAssociationTypeTranslation as BaseProductAssociationTypeTranslation;

/**
 * @MappedSuperclass
 */
class ProductAssociationTypeTranslation extends BaseProductAssociationTypeTranslation
{
}
