<?php

declare(strict_types=1);

namespace App\Entity\AdminApi;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Bundle\AdminApiBundle\Model\AuthCode as BaseAuthCode;

/**
 * @Entity
 * @Table(name="sylius_admin_api_auth_code")
 */
class AuthCode extends BaseAuthCode
{
}
