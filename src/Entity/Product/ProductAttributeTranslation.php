<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Product\Model\ProductAttributeTranslation as BaseProductAttributeTranslation;

/**
 * @MappedSuperclass
 * @Table(name="sylius_product_attribute_translation")
 */
class ProductAttributeTranslation extends BaseProductAttributeTranslation
{
}
