<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Product\Model\ProductAssociation as BaseProductAssociation;

/**
 * @MappedSuperclass
 */
class ProductAssociation extends BaseProductAssociation
{
}
