<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Sylius\Component\Core\Model\ProductTranslation as BaseProductTranslation;

class ProductTranslation extends BaseProductTranslation
{
    /** @var ?string */
    private $color = null;

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }
}
