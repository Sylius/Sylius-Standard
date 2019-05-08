<?php

declare(strict_types=1);

namespace App\Entity\Taxation;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Taxation\Model\TaxCategory as BaseTaxCategory;

/**
 * @Entity
 * @Table(name="sylius_tax_category")
 */
class TaxCategory extends BaseTaxCategory
{
}
