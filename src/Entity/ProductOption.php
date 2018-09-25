<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Product\Model\ProductOption as BaseProductOption;

/**
 * @MappedSuperclass
 */
class ProductOption extends BaseProductOption
{
}
