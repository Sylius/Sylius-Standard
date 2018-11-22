<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Product\Model\ProductOptionValue as BaseProductOptionValue;
use Sylius\Component\Product\Model\ProductOptionValueTranslationInterface;

/**
 * @MappedSuperclass
 * @Table(name="sylius_product_option_value")
 */
class ProductOptionValue extends BaseProductOptionValue
{
    protected function createTranslation(): ProductOptionValueTranslationInterface
    {
        return new ProductOptionValueTranslation();
    }
}
