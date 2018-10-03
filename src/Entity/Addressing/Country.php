<?php

declare(strict_types=1);

namespace App\Entity\Addressing;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Addressing\Model\Country as BaseCountry;

/**
 * @MappedSuperclass
 * @Table(name="sylius_country")
 */
class Country extends BaseCountry
{
}
