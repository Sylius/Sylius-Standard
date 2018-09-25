<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\User\Model\UserOAuth as BaseUserOAuth;

/**
 * @MappedSuperclass
 */
class UserOAuth extends BaseUserOAuth
{
}
