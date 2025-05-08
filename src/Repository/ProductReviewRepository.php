<?php

declare(strict_types=1);

namespace App\Repository;

use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductReviewRepository as BaseProductReviewRepository;
use Sylius\MarketplaceSuite\Component\Product\Repository\ProductReviewRepositoryInterface;
use Sylius\MarketplaceSuite\Component\Product\Repository\ProductReviewRepositoryTrait;

class ProductReviewRepository extends BaseProductReviewRepository implements ProductReviewRepositoryInterface
{
    use ProductReviewRepositoryTrait;
}
