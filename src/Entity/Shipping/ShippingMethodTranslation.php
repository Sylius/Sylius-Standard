<?php

declare(strict_types=1);

namespace App\Entity\Shipping;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Shipping\Model\ShippingMethodTranslation as BaseShippingMethodTranslation;

/**
 * @MappedSuperclass
 * @Table(name="sylius_shipping_method_translation")
 */
class ShippingMethodTranslation extends BaseShippingMethodTranslation
{
}
