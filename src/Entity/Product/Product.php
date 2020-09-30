<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Sylius\Component\Core\Model\Product as BaseProduct;

class Product extends BaseProduct
{
    const COLOR_RED   = 'red';
    const COLOR_GREEN = 'green';
    const COLOR_BLUE  = 'blue';

    /** @var string|null */
    protected $color;

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): void
    {
        $this->color = $color;
    }
}
