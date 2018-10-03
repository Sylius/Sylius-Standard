<?php

declare(strict_types=1);

namespace App\Entity\Addressing;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Addressing\Model\Province as BaseProvince;

/**
 * @MappedSuperclass
 * @Table(name="sylius_province")
 */
class Province extends BaseProvince
{
}
