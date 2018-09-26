<?php

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Product\Model\ProductOptionValueTranslation as BaseProductOptionValueTranslation;

/**
 * @MappedSuperclass
 */
class ProductOptionValueTranslation extends BaseProductOptionValueTranslation
{
}
