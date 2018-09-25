<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\Customer as BaseCustomer;

/**
 * @MappedSuperclass
 */
class Customer extends BaseCustomer
{
}
