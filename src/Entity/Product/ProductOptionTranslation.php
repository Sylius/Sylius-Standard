<?php

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Product\Model\ProductOptionTranslation as BaseProductOptionTranslation;

/**
 * @MappedSuperclass
 */
class ProductOptionTranslation extends BaseProductOptionTranslation
{
}
