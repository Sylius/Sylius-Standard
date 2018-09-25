<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\ProductTranslation as BaseProductTranslation;

/**
 * @MappedSuperclass
 */
class ProductTranslation extends BaseProductTranslation
{
}
