<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Sylius\Component\Core\Model\Product as BaseProduct;
use Sylius\Component\Product\Model\ProductTranslationInterface;

class Product extends BaseProduct
{
    public function getColor(): ?string
    {
        return $this->getTranslation()->getColor();
    }

    public function setColor(string $color): void
    {
        $this->getTranslation()->setColor($color);
    }

    protected function createTranslation(): ProductTranslationInterface
    {
        return new ProductTranslation();
    }
}
