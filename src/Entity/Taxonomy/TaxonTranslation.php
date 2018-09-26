<?php

namespace App\Entity\Taxonomy;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Taxonomy\Model\TaxonTranslation as BaseTaxonTranslation;

/**
 * @MappedSuperclass
 */
class TaxonTranslation extends BaseTaxonTranslation
{
}
