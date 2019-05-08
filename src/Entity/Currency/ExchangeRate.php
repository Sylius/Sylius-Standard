<?php

declare(strict_types=1);

namespace App\Entity\Currency;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Currency\Model\ExchangeRate as BaseExchangeRate;

/**
 * @Entity
 * @Table(name="sylius_exchange_rate")
 */
class ExchangeRate extends BaseExchangeRate
{
}
