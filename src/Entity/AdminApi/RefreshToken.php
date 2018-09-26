<?php

namespace App\Entity\AdminApi;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Bundle\AdminApiBundle\Model\RefreshToken as BaseRefreshToken;

/**
 * @MappedSuperclass
 */
class RefreshToken extends BaseRefreshToken
{
}
