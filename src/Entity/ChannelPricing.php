<?php

namespace App\Entity;

use Doctrine\ORM\Mapping\MappedSuperclass;
use Sylius\Component\Core\Model\ChannelPricing as BaseChannelPricing;

/**
 * @MappedSuperclass
 */
class ChannelPricing extends BaseChannelPricing
{
}
