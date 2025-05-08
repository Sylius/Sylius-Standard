<?php

declare(strict_types=1);

namespace App\Repository;

use Sylius\Bundle\CoreBundle\Doctrine\ORM\OrderRepository as BaseOrderRepository;
use Sylius\MarketplaceSuite\Component\Order\Repository\OrderRepositoryInterface as OrderComponentRepositoryInterface;
use Sylius\MarketplaceSuite\Component\Order\Repository\OrderRepositoryTrait as OrderComponentRepositoryTrait;
use Sylius\MarketplaceSuite\Component\Product\Repository\OrderRepositoryInterface as ProductComponentRepositoryInterface;
use Sylius\MarketplaceSuite\Component\Product\Repository\OrderRepositoryTrait as ProductComponentRepositoryTrait;

class OrderRepository extends BaseOrderRepository implements OrderComponentRepositoryInterface, ProductComponentRepositoryInterface
{
    use OrderComponentRepositoryTrait;
    use ProductComponentRepositoryTrait;
}
