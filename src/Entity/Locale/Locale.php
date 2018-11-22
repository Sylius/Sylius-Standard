<?php

declare(strict_types=1);

namespace App\Entity\Locale;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Locale\Model\Locale as BaseLocale;

/**
 * @MappedSuperclass
 * @Table(name="sylius_locale")
 */
class Locale extends BaseLocale
{
}
