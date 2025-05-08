<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Sylius\MultiSourceInventoryPlugin\Domain\Model\ProductVariantInterface;
use Sylius\MultiSourceInventoryPlugin\Domain\Model\InventorySourceStocksAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;
use Sylius\Component\Product\Model\ProductVariantTranslationInterface;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_product_variant')]
class ProductVariant extends BaseProductVariant implements ProductVariantInterface
{
    use InventorySourceStocksAwareTrait {
        __construct as private initializeInventorySourceStocksTrait;
    }
    public function __construct()
    {
        parent::__construct();
        $this->initializeInventorySourceStocksTrait();
    }
    protected function createTranslation(): ProductVariantTranslationInterface
    {
        return new ProductVariantTranslation();
    }
}
