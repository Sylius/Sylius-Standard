<?php

namespace App\Entity\Currency;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Currency\Model\ExchangeRate as BaseExchangeRate;

/**
 * @MappedSuperclass
 */
class ExchangeRate extends BaseExchangeRate
{
}
