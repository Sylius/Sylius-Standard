<?php

declare(strict_types=1);

namespace App\Entity\Customer;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\Customer as BaseCustomer;

/**
 * @Entity
 * @Table(name="sylius_customer")
 */
class Customer extends BaseCustomer
{
}
