<?php

declare(strict_types=1);

namespace App\Entity\Taxonomy;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\TaxonImage as BaseTaxonImage;

/**
 * @Entity
 * @Table(name="sylius_taxon_image")
 */
class TaxonImage extends BaseTaxonImage
{
}
