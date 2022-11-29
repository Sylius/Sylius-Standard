<?php

declare(strict_types=1);

namespace App\Entity\Addressing;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Addressing\Model\Province as BaseProvince;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_province")
 */
class Province extends BaseProvince
{
}
