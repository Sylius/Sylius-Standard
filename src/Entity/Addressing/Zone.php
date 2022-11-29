<?php

declare(strict_types=1);

namespace App\Entity\Addressing;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Addressing\Model\Zone as BaseZone;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_zone")
 */
class Zone extends BaseZone
{
}
