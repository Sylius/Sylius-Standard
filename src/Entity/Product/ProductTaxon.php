<?php

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\ProductTaxon as BaseProductTaxon;

/**
 * @MappedSuperclass
 */
class ProductTaxon extends BaseProductTaxon
{
}
