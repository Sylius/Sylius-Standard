<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Product\Model\ProductAttribute as BaseProductAttribute;

/**
 * @MappedSuperclass
 */
class ProductAttribute extends BaseProductAttribute
{
}
