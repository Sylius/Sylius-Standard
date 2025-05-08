<?php

declare(strict_types=1);

namespace App\Repository;

use Sylius\Bundle\CoreBundle\Doctrine\ORM\CustomerRepository as BaseCustomerRepository;
use Sylius\MarketplaceSuite\Component\Vendor\Repository\CustomerRepositoryInterface;
use Sylius\MarketplaceSuite\Component\Vendor\Repository\CustomerRepositoryTrait;

class CustomerRepository extends BaseCustomerRepository implements CustomerRepositoryInterface
{
    use CustomerRepositoryTrait;
}
