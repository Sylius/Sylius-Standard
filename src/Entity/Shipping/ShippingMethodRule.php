<?php

declare(strict_types=1);

namespace App\Entity\Shipping;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Shipping\Model\ShippingMethodRule as BaseShippingMethodRule;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_shipping_method_rule')]
class ShippingMethodRule extends BaseShippingMethodRule
{
}
