<?php

namespace App\Entity\Addressing;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Addressing\Model\Zone as BaseZone;

/**
 * @MappedSuperclass
 */
class Zone extends BaseZone
{
}
