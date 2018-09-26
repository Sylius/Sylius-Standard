<?php

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\ProductImage as BaseProductImage;

/**
 * @MappedSuperclass
 */
class ProductImage extends BaseProductImage
{
}
