<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\OrderSequence as BaseOrderSequence;

/**
 * @MappedSuperclass
 */
class OrderSequence extends BaseOrderSequence
{
}
