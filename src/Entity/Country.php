<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Addressing\Model\Country as BaseCountry;

/**
 * @MappedSuperclass
 */
class Country extends BaseCountry
{
}
