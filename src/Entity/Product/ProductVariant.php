<?php

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;
use Sylius\Component\Product\Model\ProductVariantTranslationInterface;

/**
 * @MappedSuperclass
 */
class ProductVariant extends BaseProductVariant
{
    protected function createTranslation(): ProductVariantTranslationInterface
    {
        return new ProductVariantTranslation();
    }
}
