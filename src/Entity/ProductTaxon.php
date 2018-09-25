<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\ProductTaxon as BaseProductTaxon;

/**
 * @MappedSuperclass
 */
class ProductTaxon extends BaseProductTaxon
{
}
