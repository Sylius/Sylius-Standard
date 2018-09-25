<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Addressing\Model\AddressLogEntry as BaseAddressLogEntry;

/**
 * @MappedSuperclass
 */
class AddressLogEntry extends BaseAddressLogEntry
{
}
