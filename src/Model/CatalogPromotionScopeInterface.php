<?php

declare(strict_types=1);

namespace App\Model;

use Sylius\Component\Core\Model\CatalogPromotionScopeInterface as BaseCatalogPromotionScopeInterface;

interface CatalogPromotionScopeInterface extends BaseCatalogPromotionScopeInterface
{
    public const TYPE_BY_PHRASE = 'by_phrase';
}
