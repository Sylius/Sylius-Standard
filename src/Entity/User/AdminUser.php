<?php

declare(strict_types=1);

namespace App\Entity\User;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\AdminUser as BaseAdminUser;

/**
 * @Entity
 * @Table(name="sylius_admin_user")
 */
class AdminUser extends BaseAdminUser
{
}
