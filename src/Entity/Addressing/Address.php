<?php

declare(strict_types=1);

namespace App\Entity\Addressing;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\Address as BaseAddress;

/**
 * @MappedSuperclass
 * @Table(name="sylius_address")
 */
class Address extends BaseAddress
{
}
