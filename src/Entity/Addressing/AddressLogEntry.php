<?php

namespace App\Entity\Addressing;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Addressing\Model\AddressLogEntry as BaseAddressLogEntry;

/**
 * @MappedSuperclass
 */
class AddressLogEntry extends BaseAddressLogEntry
{
}
