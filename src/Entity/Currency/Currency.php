<?php

namespace App\Entity\Currency;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Currency\Model\Currency as BaseCurrency;

/**
 * @MappedSuperclass
 */
class Currency extends BaseCurrency
{
}
