<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Product\Model\ProductAssociationType as BaseProductAssociationType;
use Sylius\Component\Product\Model\ProductAssociationTypeTranslationInterface;

/**
 * @MappedSuperclass
 * @Table(name="sylius_product_association_type")
 */
class ProductAssociationType extends BaseProductAssociationType
{
    protected function createTranslation(): ProductAssociationTypeTranslationInterface
    {
        return new ProductAssociationTypeTranslation();
    }
}
