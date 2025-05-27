<?php
declare(strict_types=1);

namespace App\Service\Order;

use App\Exception\ReorderException;
use Sylius\Component\Core\Model\OrderItemInterface;

interface OrderItemReorderInterface
{
    /**
     * @throws ReorderException on failure (e.g. out of stock, not owner)
     */
    public function reorder(OrderItemInterface $orderItem): void;
}
