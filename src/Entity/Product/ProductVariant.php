<?php

declare(strict_types=1);

namespace App\Entity\Product;

use BitBag\SyliusElasticsearchPlugin\Model\ProductVariantInterface as BitBagElasticsearchPluginVariant;
use BitBag\SyliusElasticsearchPlugin\Model\ProductVariantTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;
use Sylius\Component\Product\Model\ProductVariantTranslationInterface;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_product_variant')]
class ProductVariant extends BaseProductVariant implements BitBagElasticsearchPluginVariant
{
    use ProductVariantTrait;

    protected function createTranslation(): ProductVariantTranslationInterface
    {
        return new ProductVariantTranslation();
    }
}
