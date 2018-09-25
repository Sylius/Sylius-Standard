<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Addressing\Model\Zone as BaseZone;

/**
 * @MappedSuperclass
 */
class Zone extends BaseZone
{
}
