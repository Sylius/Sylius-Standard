<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\TaxRate as BaseTaxRate;

/**
 * @MappedSuperclass
 */
class TaxRate extends BaseTaxRate
{
}
