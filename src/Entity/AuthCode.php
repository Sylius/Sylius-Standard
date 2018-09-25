<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Bundle\AdminApiBundle\Model\AuthCode as BaseAuthCode;

/**
 * @MappedSuperclass
 */
class AuthCode extends BaseAuthCode
{
}
