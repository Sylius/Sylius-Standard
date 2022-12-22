<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductImage as BaseProductImage;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product_image")
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_product_image')]
class ProductImage extends BaseProductImage
{
}
