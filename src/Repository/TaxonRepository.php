<?php

declare(strict_types=1);

namespace App\Repository;

use Sylius\Bundle\TaxonomyBundle\Doctrine\ORM\TaxonRepository as BaseTaxonRepository;
use Sylius\MarketplaceSuite\Component\Vendor\Repository\TaxonRepositoryInterface;
use Sylius\MarketplaceSuite\Component\Vendor\Repository\TaxonRepositoryTrait;

class TaxonRepository extends BaseTaxonRepository implements TaxonRepositoryInterface
{
    use TaxonRepositoryTrait;
}
