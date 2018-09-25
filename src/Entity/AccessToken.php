<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Bundle\AdminApiBundle\Model\AccessToken as BaseAccessToken;

/**
 * @MappedSuperclass
 */
class AccessToken extends BaseAccessToken
{
}
