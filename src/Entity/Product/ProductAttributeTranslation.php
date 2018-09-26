<?php

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Product\Model\ProductAttributeTranslation as BaseProductAttributeTranslation;

/**
 * @MappedSuperclass
 */
class ProductAttributeTranslation extends BaseProductAttributeTranslation
{
}
