<?php

declare(strict_types=1);

namespace App\Entity\User;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\AdminUser as BaseAdminUser;

/**
 * @MappedSuperclass
 * @Table(name="sylius_admin_user")
 */
class AdminUser extends BaseAdminUser
{
}
