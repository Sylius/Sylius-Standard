<?php

declare(strict_types=1);

namespace App\Entity\Currency;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Currency\Model\Currency as BaseCurrency;

/**
 * @MappedSuperclass
 * @Table(name="sylius_currency")
 */
class Currency extends BaseCurrency
{
}
