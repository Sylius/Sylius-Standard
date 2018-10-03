<?php

declare(strict_types=1);

namespace App\Entity\Shipping;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\ShippingMethod as BaseShippingMethod;
use Sylius\Component\Shipping\Model\ShippingMethodTranslationInterface;

/**
 * @MappedSuperclass
 * @Table(name="sylius_shipping_method")
 */
class ShippingMethod extends BaseShippingMethod
{
    protected function createTranslation(): ShippingMethodTranslationInterface
    {
        return new ShippingMethodTranslation();
    }
}
