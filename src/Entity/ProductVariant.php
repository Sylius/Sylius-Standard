<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;

/**
 * @MappedSuperclass
 */
class ProductVariant extends BaseProductVariant
{
}
