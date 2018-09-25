<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\TaxonImage as BaseTaxonImage;

/**
 * @MappedSuperclass
 */
class TaxonImage extends BaseTaxonImage
{
}
