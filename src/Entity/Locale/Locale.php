<?php

namespace App\Entity\Locale;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Locale\Model\Locale as BaseLocale;

/**
 * @MappedSuperclass
 */
class Locale extends BaseLocale
{
}
