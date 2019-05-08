<?php

declare(strict_types=1);

namespace App\Entity\User;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\ShopUser as BaseShopUser;

/**
 * @Entity
 * @Table(name="sylius_shop_user")
 */
class ShopUser extends BaseShopUser
{
}
