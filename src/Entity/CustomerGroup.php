<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Customer\Model\CustomerGroup as BaseCustomerGroup;

/**
 * @MappedSuperclass
 */
class CustomerGroup extends BaseCustomerGroup
{
}
