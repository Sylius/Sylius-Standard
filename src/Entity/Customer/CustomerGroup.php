<?php

namespace App\Entity\Customer;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Customer\Model\CustomerGroup as BaseCustomerGroup;

/**
 * @MappedSuperclass
 */
class CustomerGroup extends BaseCustomerGroup
{
}
