<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Tests\Application\Entity;

use Sylius\Component\Core\Model\Order as BaseOrder;
use SyliusOrderNotePlugin\Entity\OrderNoteAwareInterface;
use SyliusOrderNotePlugin\Entity\OrderNoteAwareTrait;

class Order extends BaseOrder implements OrderNoteAwareInterface
{
    use OrderNoteAwareTrait;
}
