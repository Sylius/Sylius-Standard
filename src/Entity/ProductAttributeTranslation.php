<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Product\Model\ProductAttributeTranslation as BaseProductAttributeTranslation;

/**
 * @MappedSuperclass
 */
class ProductAttributeTranslation extends BaseProductAttributeTranslation
{
}
