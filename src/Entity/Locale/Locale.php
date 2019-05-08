<?php

declare(strict_types=1);

namespace App\Entity\Locale;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Locale\Model\Locale as BaseLocale;

/**
 * @Entity
 * @Table(name="sylius_locale")
 */
class Locale extends BaseLocale
{
}
