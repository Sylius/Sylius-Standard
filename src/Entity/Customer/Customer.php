<?php

declare(strict_types=1);

namespace App\Entity\Customer;

use Sylius\B2BKit\Entity\CustomerInterface;
use Sylius\B2BKit\Entity\CustomerAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Customer as BaseCustomer;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_customer')]
class Customer extends BaseCustomer implements CustomerInterface
{
    use CustomerAwareTrait;
}
