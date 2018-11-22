<?php

declare(strict_types=1);

namespace App\Entity\Addressing;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Addressing\Model\Zone as BaseZone;

/**
 * @MappedSuperclass
 * @Table(name="sylius_zone")
 */
class Zone extends BaseZone
{
}
