<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Product\Model\ProductOptionValueTranslation as BaseProductOptionValueTranslation;

/**
 * @Entity
 * @Table(name="sylius_product_option_value_translation")
 */
class ProductOptionValueTranslation extends BaseProductOptionValueTranslation
{
}
