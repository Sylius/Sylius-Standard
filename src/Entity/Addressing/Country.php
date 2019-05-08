<?php

declare(strict_types=1);

namespace App\Entity\Addressing;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Addressing\Model\Country as BaseCountry;

/**
 * @Entity
 * @Table(name="sylius_country")
 */
class Country extends BaseCountry
{
}
