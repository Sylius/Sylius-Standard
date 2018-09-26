<?php

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Product\Model\ProductAssociationType as BaseProductAssociationType;
use Sylius\Component\Product\Model\ProductAssociationTypeTranslationInterface;

/**
 * @MappedSuperclass
 */
class ProductAssociationType extends BaseProductAssociationType
{
    protected function createTranslation(): ProductAssociationTypeTranslationInterface
    {
        return new ProductAssociationTypeTranslation();
    }
}
