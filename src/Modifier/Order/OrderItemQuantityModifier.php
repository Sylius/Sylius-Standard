<?php

declare(strict_types=1);

namespace App\Modifier\Order;

use Sylius\Component\Order\Model\OrderItemInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;

class OrderItemQuantityModifier implements OrderItemQuantityModifierInterface
{
    public function __construct(
        private OrderItemQuantityModifierInterface $decorated,
        private int $quantityMinimum,
    )
    {
    }

    public function modify(OrderItemInterface $orderItem, int $targetQuantity): void
    {
        $targetQuantity = \max($targetQuantity, $this->quantityMinimum);

        $this->decorated->modify($orderItem, $targetQuantity);
    }
}
