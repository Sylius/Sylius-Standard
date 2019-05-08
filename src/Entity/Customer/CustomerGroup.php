<?php

declare(strict_types=1);

namespace App\Entity\Customer;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Customer\Model\CustomerGroup as BaseCustomerGroup;

/**
 * @Entity
 * @Table(name="sylius_customer_group")
 */
class CustomerGroup extends BaseCustomerGroup
{
}
