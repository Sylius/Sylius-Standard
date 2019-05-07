<?php

declare(strict_types=1);

namespace App\Entity\Currency;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Currency\Model\Currency as BaseCurrency;

/**
 * @Entity
 * @Table(name="sylius_currency")
 */
class Currency extends BaseCurrency
{
}
