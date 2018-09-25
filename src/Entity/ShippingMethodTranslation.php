<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Shipping\Model\ShippingMethodTranslation as BaseShippingMethodTranslation;

/**
 * @MappedSuperclass
 */
class ShippingMethodTranslation extends BaseShippingMethodTranslation
{
}
