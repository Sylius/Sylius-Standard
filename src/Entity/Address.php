<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\Address as BaseAddress;

/**
 * @MappedSuperclass
 */
class Address extends BaseAddress
{
}
