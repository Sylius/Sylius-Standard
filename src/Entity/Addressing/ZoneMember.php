<?php

declare(strict_types=1);

namespace App\Entity\Addressing;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Addressing\Model\ZoneMember as BaseZoneMember;

/**
 * @Entity
 * @Table(name="sylius_zone_member")
 */
class ZoneMember extends BaseZoneMember
{
}
