<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Product\Model\ProductAssociation as BaseProductAssociation;

/**
 * @MappedSuperclass
 * @Table(name="sylius_product_association")
 */
class ProductAssociation extends BaseProductAssociation
{
}
