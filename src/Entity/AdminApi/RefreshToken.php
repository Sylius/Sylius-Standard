<?php

declare(strict_types=1);

namespace App\Entity\AdminApi;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Bundle\AdminApiBundle\Model\RefreshToken as BaseRefreshToken;

/**
 * @Entity
 * @Table(name="sylius_admin_api_refresh_token")
 */
class RefreshToken extends BaseRefreshToken
{
}
