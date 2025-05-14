<?php

declare(strict_types=1);

namespace App\Entity\Channel;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ShopBillingData as BaseShopBillingData;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_shop_billing_data')]
class ShopBillingData extends BaseShopBillingData
{
}
