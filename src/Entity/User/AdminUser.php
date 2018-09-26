<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\AdminUser as BaseAdminUser;

/**
 * @MappedSuperclass
 */
class AdminUser extends BaseAdminUser
{
}
