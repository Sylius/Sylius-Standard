<?php

declare(strict_types=1);

namespace App\Entity\Channel;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ChannelPriceHistoryConfig as BaseChannelPriceHistoryConfig;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_channel_price_history_config")
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_channel_price_history_config')]
class ChannelPriceHistoryConfig extends BaseChannelPriceHistoryConfig
{
}
