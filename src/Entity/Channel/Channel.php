<?php

declare(strict_types=1);

namespace App\Entity\Channel;

use Sylius\ReturnPlugin\Domain\Model\ChannelInterface;
use Sylius\ReturnPlugin\Domain\Model\ReturnRequestsAllowedAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Channel as BaseChannel;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_channel')]
class Channel extends BaseChannel implements ChannelInterface
{
    use ReturnRequestsAllowedAwareTrait;
}
