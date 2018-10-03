<?php

declare(strict_types=1);

namespace App\Entity\Taxonomy;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Taxonomy\Model\TaxonTranslation as BaseTaxonTranslation;

/**
 * @MappedSuperclass
 * @Table(name="sylius_taxon_translation")
 */
class TaxonTranslation extends BaseTaxonTranslation
{
}
