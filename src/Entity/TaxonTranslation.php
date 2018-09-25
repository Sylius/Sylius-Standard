<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Taxonomy\Model\TaxonTranslation as BaseTaxonTranslation;

/**
 * @MappedSuperclass
 */
class TaxonTranslation extends BaseTaxonTranslation
{
}
