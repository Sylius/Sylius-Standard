<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\ShopUser as BaseShopUser;

/**
 * @MappedSuperclass
 */
class ShopUser extends BaseShopUser
{
}
