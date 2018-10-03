<?php

declare(strict_types=1);

namespace App\Entity\Customer;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Customer\Model\CustomerGroup as BaseCustomerGroup;

/**
 * @MappedSuperclass
 * @Table(name="sylius_customer_group")
 */
class CustomerGroup extends BaseCustomerGroup
{
}
