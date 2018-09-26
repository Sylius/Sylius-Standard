<?php

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Product\Model\ProductAttributeValue as BaseProductAttributeValue;

/**
 * @MappedSuperclass
 */
class ProductAttributeValue extends BaseProductAttributeValue
{
}
