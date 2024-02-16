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
#[ORM\Entity]
#[ORM\Table(name: 'sylius_product')]
class Product extends BaseProduct
{
    public const COLOURS = ['Blue' => 1, 'Red' => 2, 'Green' => 3];

    /**
     * @var string|null
     *
     * @ORM\Column(name="colour", type="string", nullable=true)
     */
    private ?string $colour;

    public function getColour(): ?string
    {
        return $this->colour;
    }

    /**
     * @param string|null $colour
     * @return void
     */
    public function setColour(?string $colour): void
    {
        $this->colour = $colour;
    }

    protected function createTranslation(): ProductTranslationInterface
    {
        return new ProductTranslation();
    }
}
