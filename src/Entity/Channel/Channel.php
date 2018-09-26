<?php

namespace App\Entity\Channel;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\Channel as BaseChannel;

/**
 * @MappedSuperclass
 */
class Channel extends BaseChannel
{
}
