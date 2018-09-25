<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Product\Model\ProductAssociationType as BaseProductAssociationType;

/**
 * @MappedSuperclass
 */
class ProductAssociationType extends BaseProductAssociationType
{
}
