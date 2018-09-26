<?php

namespace App\Entity\Customer;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\Customer as BaseCustomer;

/**
 * @MappedSuperclass
 */
class Customer extends BaseCustomer
{
}
