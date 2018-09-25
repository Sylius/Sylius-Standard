<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Addressing\Model\ZoneMember as BaseZoneMember;

/**
 * @MappedSuperclass
 */
class ZoneMember extends BaseZoneMember
{
}
