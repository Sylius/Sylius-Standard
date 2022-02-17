<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Sylius\Component\Core\Model\Product as BaseProduct;
use Sylius\Component\Product\Model\ProductTranslation;
use Sylius\Component\Product\Model\ProductTranslationInterface;

class Product extends BaseProduct
{
    const COLOR_RED = 'RED';
    const COLOR_BLUE = 'BLUE';
    const COLOR_GREEN = 'GREEN';

    /** @var string $color */
    protected string $color;

    protected function createTranslation(): ProductTranslationInterface
    {
        return new ProductTranslation();
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): void
    {
        $this->color = $color;
    }
}
