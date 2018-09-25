<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Product\Model\ProductAssociationTypeTranslation as BaseProductAssociationTypeTranslation;

/**
 * @MappedSuperclass
 */
class ProductAssociationTypeTranslation extends BaseProductAssociationTypeTranslation
{
}
