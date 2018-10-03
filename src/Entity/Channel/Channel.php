<?php

declare(strict_types=1);

namespace App\Entity\Channel;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\Channel as BaseChannel;

/**
 * @MappedSuperclass
 * @Table(name="sylius_channel")
 */
class Channel extends BaseChannel
{
}
