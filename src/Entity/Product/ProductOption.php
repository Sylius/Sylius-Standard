<?php

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Product\Model\ProductOption as BaseProductOption;
use Sylius\Component\Product\Model\ProductOptionTranslationInterface;

/**
 * @MappedSuperclass
 */
class ProductOption extends BaseProductOption
{
    protected function createTranslation(): ProductOptionTranslationInterface
    {
        return new ProductOptionTranslation();
    }
}
