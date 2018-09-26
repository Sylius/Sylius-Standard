<?php

namespace App\Entity\Addressing;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Addressing\Model\Province as BaseProvince;

/**
 * @MappedSuperclass
 */
class Province extends BaseProvince
{
}
