<?php

declare(strict_types=1);

namespace App\Entity\Customer;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\Customer as BaseCustomer;

/**
 * @MappedSuperclass
 * @Table(name="sylius_customer")
 */
class Customer extends BaseCustomer
{
}
