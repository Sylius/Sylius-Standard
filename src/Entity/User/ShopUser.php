<?php

declare(strict_types=1);

namespace App\Entity\User;

use Sylius\B2BKit\Entity\ShopUserInterface;
use Sylius\B2BKit\Entity\ShopUserAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ShopUser as BaseShopUser;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_shop_user')]
class ShopUser extends BaseShopUser implements ShopUserInterface
{
    use ShopUserAwareTrait;
}
