<?php

declare(strict_types=1);

namespace App\Entity\Channel;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\ChannelPricing as BaseChannelPricing;

/**
 * @MappedSuperclass
 * @Table(name="sylius_channel_pricing")
 */
class ChannelPricing extends BaseChannelPricing
{
}
