<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\ShippingMethod as BaseShippingMethod;

/**
 * @MappedSuperclass
 */
class ShippingMethod extends BaseShippingMethod
{
}
