<?php

declare(strict_types=1);

namespace App\Entity\Addressing;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Addressing\Model\Province as BaseProvince;

/**
 * @Entity
 * @Table(name="sylius_province")
 */
class Province extends BaseProvince
{
}
