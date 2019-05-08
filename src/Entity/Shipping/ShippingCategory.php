<?php

declare(strict_types=1);

namespace App\Entity\Shipping;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Shipping\Model\ShippingCategory as BaseShippingCategory;

/**
 * @Entity
 * @Table(name="sylius_shipping_category")
 */
class ShippingCategory extends BaseShippingCategory
{
}
