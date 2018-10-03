<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\Product as BaseProduct;
use Sylius\Component\Product\Model\ProductTranslationInterface;

/**
 * @MappedSuperclass
 * @Table(name="sylius_product")
 */
class Product extends BaseProduct
{
    protected function createTranslation(): ProductTranslationInterface
    {
        return new ProductTranslation();
    }
}
