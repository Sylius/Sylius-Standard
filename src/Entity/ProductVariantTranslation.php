<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Product\Model\ProductVariantTranslation as BaseProductVariantTranslation;

/**
 * @MappedSuperclass
 */
class ProductVariantTranslation extends BaseProductVariantTranslation
{
}
