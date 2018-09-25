<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Addressing\Model\Province as BaseProvince;

/**
 * @MappedSuperclass
 */
class Province extends BaseProvince
{
}
