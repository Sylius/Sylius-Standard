<?php

declare(strict_types=1);

namespace App\Entity\Taxation;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\TaxRate as BaseTaxRate;

/**
 * @Entity
 * @Table(name="sylius_tax_rate")
 */
class TaxRate extends BaseTaxRate
{
}
