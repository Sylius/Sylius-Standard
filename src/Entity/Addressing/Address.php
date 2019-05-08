<?php

declare(strict_types=1);

namespace App\Entity\Addressing;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\Address as BaseAddress;

/**
 * @Entity
 * @Table(name="sylius_address")
 */
class Address extends BaseAddress
{
}
