<?php

declare(strict_types=1);

namespace App\Entity\AdminApi;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Bundle\AdminApiBundle\Model\RefreshToken as BaseRefreshToken;

/**
 * @MappedSuperclass
 * @Table(name="sylius_admin_api_refresh_token")
 */
class RefreshToken extends BaseRefreshToken
{
}
