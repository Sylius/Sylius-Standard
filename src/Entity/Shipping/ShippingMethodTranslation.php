<?php

declare(strict_types=1);

namespace App\Entity\Shipping;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Shipping\Model\ShippingMethodTranslation as BaseShippingMethodTranslation;

/**
 * @Entity
 * @Table(name="sylius_shipping_method_translation")
 */
class ShippingMethodTranslation extends BaseShippingMethodTranslation
{
}
