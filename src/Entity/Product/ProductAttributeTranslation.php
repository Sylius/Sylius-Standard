<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Product\Model\ProductAttributeTranslation as BaseProductAttributeTranslation;

/**
 * @Entity
 * @Table(name="sylius_product_attribute_translation")
 */
class ProductAttributeTranslation extends BaseProductAttributeTranslation
{
}
