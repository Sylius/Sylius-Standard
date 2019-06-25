<?php

declare(strict_types=1);

namespace App\Entity\AdminApi;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Bundle\AdminApiBundle\Model\Client as BaseClient;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_admin_api_client")
 */
class Client extends BaseClient
{
}
