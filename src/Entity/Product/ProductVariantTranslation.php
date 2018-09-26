<?php

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Product\Model\ProductVariantTranslation as BaseProductVariantTranslation;

/**
 * @MappedSuperclass
 */
class ProductVariantTranslation extends BaseProductVariantTranslation
{
}
