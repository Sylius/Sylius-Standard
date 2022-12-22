<?php

declare(strict_types=1);

namespace App\Entity\Customer;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Customer\Model\CustomerGroup as BaseCustomerGroup;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_customer_group")
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_customer_group')]
class CustomerGroup extends BaseCustomerGroup
{
}
