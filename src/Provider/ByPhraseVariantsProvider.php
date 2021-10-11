<?php

namespace App\Provider;

use Sylius\Bundle\CoreBundle\Provider\VariantsProviderInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Webmozart\Assert\Assert;
use Sylius\Component\Core\Model\CatalogPromotionScopeInterface;

class ByPhraseVariantsProvider implements VariantsProviderInterface
{
    private ProductVariantRepositoryInterface $productVariantRepository;

    public function __construct(ProductVariantRepositoryInterface $productVariantRepository)
    {
        $this->productVariantRepository = $productVariantRepository;
    }

    public function supports(CatalogPromotionScopeInterface $catalogPromotionScopeType): bool
    {
        return $catalogPromotionScopeType->getType() === \App\Model\CatalogPromotionScopeInterface::TYPE_BY_PHRASE;
    }

    public function provideEligibleVariants(CatalogPromotionScopeInterface $scope): array
    {
        $configuration = $scope->getConfiguration();
        Assert::keyExists($configuration, 'phrase', 'This rule should have configured phrase');

        return $this->productVariantRepository->findByPhrase($configuration['phrase'], 'en_US');
    }
}
