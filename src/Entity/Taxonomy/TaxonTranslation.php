<?php

declare(strict_types=1);

namespace App\Entity\Taxonomy;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Taxonomy\Model\TaxonTranslation as BaseTaxonTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_taxon_translation")
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_taxon_translation')]
class TaxonTranslation extends BaseTaxonTranslation
{
}
