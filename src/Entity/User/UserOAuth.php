<?php

namespace App\Entity\User;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\User\Model\UserOAuth as BaseUserOAuth;

/**
 * @MappedSuperclass
 */
class UserOAuth extends BaseUserOAuth
{
}
