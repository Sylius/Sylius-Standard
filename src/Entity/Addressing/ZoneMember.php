<?php

declare(strict_types=1);

namespace App\Entity\Addressing;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Addressing\Model\ZoneMember as BaseZoneMember;

/**
 * @MappedSuperclass
 * @Table(name="sylius_zone_member")
 */
class ZoneMember extends BaseZoneMember
{
}
