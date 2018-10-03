<?php

declare(strict_types=1);

namespace App\Entity\Taxonomy;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\Taxon as BaseTaxon;
use Sylius\Component\Taxonomy\Model\TaxonTranslationInterface;

/**
 * @MappedSuperclass
 * @Table(name="sylius_taxon")
 */
class Taxon extends BaseTaxon
{
    protected function createTranslation(): TaxonTranslationInterface
    {
        return new TaxonTranslation();
    }
}
