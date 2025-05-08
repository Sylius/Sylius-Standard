<?php

declare(strict_types=1);

namespace App\Repository;

use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductRepository as BaseProductRepository;
use Sylius\MarketplaceSuite\Component\Product\Repository\ProductRepositoryInterface;
use Sylius\MarketplaceSuite\Component\Product\Repository\ProductRepositoryTrait;

class ProductRepository extends BaseProductRepository implements ProductRepositoryInterface
{
    use ProductRepositoryTrait;
}
