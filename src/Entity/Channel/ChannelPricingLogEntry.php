<?php

declare(strict_types=1);

namespace App\Entity\Channel;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ChannelPricingLogEntry as BaseChannelPricingLogEntry;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_channel_pricing_log_entry")
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_channel_pricing_log_entry')]
class ChannelPricingLogEntry extends BaseChannelPricingLogEntry
{
}
