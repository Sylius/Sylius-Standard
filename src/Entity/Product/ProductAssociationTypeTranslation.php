<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Product\Model\ProductAssociationTypeTranslation as BaseProductAssociationTypeTranslation;

/**
 * @Entity
 * @Table(name="sylius_product_association_type_translation")
 */
class ProductAssociationTypeTranslation extends BaseProductAssociationTypeTranslation
{
}
