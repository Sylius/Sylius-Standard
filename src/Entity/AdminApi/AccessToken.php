<?php

declare(strict_types=1);

namespace App\Entity\AdminApi;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Bundle\AdminApiBundle\Model\AccessToken as BaseAccessToken;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_admin_api_access_token")
 */
class AccessToken extends BaseAccessToken
{
}
