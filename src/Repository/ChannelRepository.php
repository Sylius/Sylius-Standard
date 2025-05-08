<?php

declare(strict_types=1);

namespace App\Repository;

use Sylius\Bundle\ChannelBundle\Doctrine\ORM\ChannelRepository as BaseChannelRepository;
use Sylius\MarketplaceSuite\Component\Settlement\Repository\ChannelRepositoryInterface;
use Sylius\MarketplaceSuite\Component\Settlement\Repository\ChannelRepositoryTrait;

class ChannelRepository extends BaseChannelRepository implements ChannelRepositoryInterface
{
    use ChannelRepositoryTrait;
}
