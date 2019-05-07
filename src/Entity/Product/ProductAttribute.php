<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Attribute\Model\AttributeTranslationInterface;
use Sylius\Component\Product\Model\ProductAttribute as BaseProductAttribute;

/**
 * @Entity
 * @Table(name="sylius_product_attribute")
 */
class ProductAttribute extends BaseProductAttribute
{
    protected function createTranslation(): AttributeTranslationInterface
    {
        return new ProductAttributeTranslation();
    }
}
