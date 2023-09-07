<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Attribute\Model\AttributeTranslationInterface;
use Sylius\Component\Product\Model\ProductAttribute as BaseProductAttribute;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_attribute")
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_product_attribute')]
class ProductAttribute extends BaseProductAttribute
{
    protected function createTranslation(): AttributeTranslationInterface
    {
        return new ProductAttributeTranslation();
    }
}
