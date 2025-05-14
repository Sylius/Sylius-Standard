<?php

declare(strict_types=1);

namespace App\Entity\Order;

use Sylius\B2BKit\Entity\OrderInterface;
use Sylius\B2BKit\Entity\OrderAwareTrait;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Order as BaseOrder;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_order')]
class Order extends BaseOrder implements OrderInterface
{
    use OrderAwareTrait;
}
