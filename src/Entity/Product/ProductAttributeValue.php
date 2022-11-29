<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Product\Model\ProductAttributeValue as BaseProductAttributeValue;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_attribute_value")
 */
class ProductAttributeValue extends BaseProductAttributeValue
{
}
