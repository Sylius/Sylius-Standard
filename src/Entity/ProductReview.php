<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\ProductReview as BaseProductReview;

/**
 * @MappedSuperclass
 */
class ProductReview extends BaseProductReview
{
}
