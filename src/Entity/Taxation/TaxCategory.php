<?php

namespace App\Entity\Taxation;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Taxation\Model\TaxCategory as BaseTaxCategory;

/**
 * @MappedSuperclass
 */
class TaxCategory extends BaseTaxCategory
{
}
