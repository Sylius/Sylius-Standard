<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Product\Model\ProductAssociation as BaseProductAssociation;

/**
 * @Entity
 * @Table(name="sylius_product_association")
 */
class ProductAssociation extends BaseProductAssociation
{
}
