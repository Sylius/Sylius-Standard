<?php

declare(strict_types=1);

namespace App\Repository;

use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductVariantRepository as BaseProductVariantRepository;
use Sylius\MarketplaceSuite\Component\Product\Repository\ProductVariantRepositoryInterface;
use Sylius\MarketplaceSuite\Component\Product\Repository\ProductVariantRepositoryTrait;

class ProductVariantRepository extends BaseProductVariantRepository implements ProductVariantRepositoryInterface
{
    use ProductVariantRepositoryTrait;
}
