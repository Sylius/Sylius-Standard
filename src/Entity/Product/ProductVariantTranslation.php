<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Product\Model\ProductVariantTranslation as BaseProductVariantTranslation;

/**
 * @MappedSuperclass
 * @Table(name="sylius_product_variant_translation")
 */
class ProductVariantTranslation extends BaseProductVariantTranslation
{
}
