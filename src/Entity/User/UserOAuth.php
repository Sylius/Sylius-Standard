<?php

declare(strict_types=1);

namespace App\Entity\User;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\User\Model\UserOAuth as BaseUserOAuth;

/**
 * @Entity
 * @Table(name="sylius_user_oauth")
 */
class UserOAuth extends BaseUserOAuth
{
}
