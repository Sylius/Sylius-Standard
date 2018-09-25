<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Product\Model\ProductOptionValue as BaseProductOptionValue;

/**
 * @MappedSuperclass
 */
class ProductOptionValue extends BaseProductOptionValue
{
}
