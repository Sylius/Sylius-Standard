<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\Product as BaseProduct;

/**
 * @MappedSuperclass
 */
class Product extends BaseProduct
{
}
