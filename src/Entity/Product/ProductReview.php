<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\ProductReview as BaseProductReview;

/**
 * @MappedSuperclass
 * @Table(name="sylius_product_review")
 */
class ProductReview extends BaseProductReview
{
}
