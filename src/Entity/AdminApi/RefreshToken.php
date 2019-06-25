<?php

declare(strict_types=1);

namespace App\Entity\AdminApi;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Bundle\AdminApiBundle\Model\RefreshToken as BaseRefreshToken;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_admin_api_refresh_token")
 */
class RefreshToken extends BaseRefreshToken
{
}
