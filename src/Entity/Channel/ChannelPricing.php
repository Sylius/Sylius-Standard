<?php

declare(strict_types=1);

namespace App\Entity\Channel;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Sylius\Component\Core\Model\ChannelPricing as BaseChannelPricing;

/**
 * @Entity
 * @Table(name="sylius_channel_pricing")
 */
class ChannelPricing extends BaseChannelPricing
{
}
