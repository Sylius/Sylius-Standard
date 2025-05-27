<?php
declare(strict_types=1);

namespace App\Service\Order;

use App\Exception\ReorderException;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Modifier\OrderModifierInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final readonly class OrderItemReorder implements OrderItemReorderInterface
{
    public function __construct(
        private CartContextInterface               $cartContext,
        private OrderItemQuantityModifierInterface $quantityModifier,
        private OrderModifierInterface             $orderModifier,
        private OrderProcessorInterface            $orderProcessor,
        private OrderItemReorderValidatorInterface $orderItemReorderValidator,
        private FactoryInterface                   $orderItemFactory,
        private OrderRepositoryInterface           $orderRepository
    )
    {
    }

    public function reorder(OrderItemInterface $orderItem): void
    {
        $this->orderItemReorderValidator->validate($orderItem);

        $variant = $orderItem->getVariant();
        if (null === $variant) {
            throw new ReorderException('Product variant not available.');
        }

        $quantity = $orderItem->getQuantity();
        if ($quantity <= 0) {
            throw new ReorderException('Quantity must be greater than 0.');
        }

        $order = $this->cartContext->getCart();
        $existingItem = $this->findItemInOrderByVariant($order, $variant);

        if ($existingItem) {
            $this->quantityModifier->modify($existingItem, $existingItem->getQuantity() + $quantity);
        } else {
            /** @var OrderItemInterface $newItem */
            $newItem = $this->orderItemFactory->createNew();
            $newItem->setVariant($variant);

            $this->quantityModifier->modify($newItem, $quantity);
            $this->orderModifier->addToOrder($order, $newItem);
        }

        $this->orderProcessor->process($order);
        $this->orderRepository->add($order);
    }

    private function findItemInOrderByVariant(OrderInterface $order, ProductVariantInterface $variant): ?OrderItemInterface
    {
        foreach ($order->getItems() as $item) {
            if ($item->getVariant() === $variant) {
                return $item;
            }
        }

        return null;
    }
}
