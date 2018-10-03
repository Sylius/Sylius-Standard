<?php

declare(strict_types=1);

namespace App\Entity\Taxonomy;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\TaxonImage as BaseTaxonImage;

/**
 * @MappedSuperclass
 * @Table(name="sylius_taxon_image")
 */
class TaxonImage extends BaseTaxonImage
{
}
