<?php

namespace App\Entity\AdminApi;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Bundle\AdminApiBundle\Model\Client as BaseClient;

/**
 * @MappedSuperclass
 */
class Client extends BaseClient
{
}
