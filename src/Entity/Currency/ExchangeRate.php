<?php

declare(strict_types=1);

namespace App\Entity\Currency;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Currency\Model\ExchangeRate as BaseExchangeRate;

/**
 * @MappedSuperclass
 * @Table(name="sylius_exchange_rate")
 */
class ExchangeRate extends BaseExchangeRate
{
}
