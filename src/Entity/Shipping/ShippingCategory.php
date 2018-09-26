<?php

namespace App\Entity\Shipping;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Shipping\Model\ShippingCategory as BaseShippingCategory;

/**
 * @MappedSuperclass
 */
class ShippingCategory extends BaseShippingCategory
{
}
