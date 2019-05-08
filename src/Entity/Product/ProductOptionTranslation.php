<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Product\Model\ProductOptionTranslation as BaseProductOptionTranslation;

/**
 * @Entity
 * @Table(name="sylius_product_option_translation")
 */
class ProductOptionTranslation extends BaseProductOptionTranslation
{
}
