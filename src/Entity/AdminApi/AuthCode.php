<?php

declare(strict_types=1);

namespace App\Entity\AdminApi;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Bundle\AdminApiBundle\Model\AuthCode as BaseAuthCode;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_admin_api_auth_code")
 */
class AuthCode extends BaseAuthCode
{
}
