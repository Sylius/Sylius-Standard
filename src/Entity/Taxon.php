<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\Taxon as BaseTaxon;

/**
 * @MappedSuperclass
 */
class Taxon extends BaseTaxon
{
}
