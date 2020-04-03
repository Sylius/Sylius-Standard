<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Product as BaseProduct;
use Sylius\Component\Product\Model\ProductTranslationInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_product")
 */
class Product extends BaseProduct
{
    /**
     * @var ProductColor
     *
     * @ORM\Column(type="product_color", name="color")
     */
    protected $color;

    protected function createTranslation(): ProductTranslationInterface
    {
        return new ProductTranslation();
    }

    public function getColor(): ?ProductColor
    {
        return $this->color;
    }

    public function setColor(ProductColor $color): void
    {
        $this->color = $color;
    }
}
