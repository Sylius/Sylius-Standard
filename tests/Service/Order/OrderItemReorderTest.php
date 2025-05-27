<?php
declare(strict_types=1);

namespace App\Tests\Service\Order;

use App\Exception\ReorderException;
use App\Service\Order\OrderItemReorder;
use App\Service\Order\OrderItemReorderValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Modifier\OrderModifierInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Doctrine\Common\Collections\ArrayCollection;


class OrderItemReorderTest extends TestCase
{
    private OrderItemReorder $reorderService;
    private MockObject $cartContext;
    private MockObject $quantityModifier;
    private MockObject $orderModifier;
    private MockObject $orderProcessor;
    private MockObject $orderItemReorderValidator;
    private MockObject $orderItemFactory;
    private MockObject $orderRepository;

    protected function setUp(): void
    {
        $this->cartContext = $this->createMock(CartContextInterface::class);
        $this->quantityModifier = $this->createMock(OrderItemQuantityModifierInterface::class);
        $this->orderModifier = $this->createMock(OrderModifierInterface::class);
        $this->orderProcessor = $this->createMock(OrderProcessorInterface::class);
        $this->orderItemReorderValidator = $this->createMock(OrderItemReorderValidatorInterface::class);
        $this->orderItemFactory = $this->createMock(FactoryInterface::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);

        $this->reorderService = new OrderItemReorder(
            $this->cartContext,
            $this->quantityModifier,
            $this->orderModifier,
            $this->orderProcessor,
            $this->orderItemReorderValidator,
            $this->orderItemFactory,
            $this->orderRepository
        );
    }

    public function testReorderSuccessWithNewItem(): void
    {
        $orderItem = $this->createMock(OrderItemInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $newItem = $this->createMock(OrderItemInterface::class);

        $orderItem->method('getVariant')->willReturn($variant);
        $orderItem->method('getQuantity')->willReturn(2);

        $this->cartContext->method('getCart')->willReturn($order);
        $order->method('getItems')->willReturn(new ArrayCollection([]));
        $this->orderItemFactory->method('createNew')->willReturn($newItem);

        $this->orderItemReorderValidator->expects($this->once())
            ->method('validate')
            ->with($orderItem);

        $newItem->expects($this->once())
            ->method('setVariant')
            ->with($variant);

        $this->quantityModifier->expects($this->once())
            ->method('modify')
            ->with($newItem, 2);

        $this->orderModifier->expects($this->once())
            ->method('addToOrder')
            ->with($order, $newItem);

        $this->orderProcessor->expects($this->once())
            ->method('process')
            ->with($order);

        $this->orderRepository->expects($this->once())
            ->method('add')
            ->with($order);

        $this->reorderService->reorder($orderItem);
    }

    public function testReorderSuccessWithExistingItem(): void
    {
        $orderItem = $this->createMock(OrderItemInterface::class);
        $existingItem = $this->createMock(OrderItemInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);
        $order = $this->createMock(OrderInterface::class);

        $orderItem->method('getVariant')->willReturn($variant);
        $orderItem->method('getQuantity')->willReturn(2);
        $existingItem->method('getVariant')->willReturn($variant);
        $existingItem->method('getQuantity')->willReturn(3);

        $this->cartContext->method('getCart')->willReturn($order);
        $order->method('getItems')->willReturn(new ArrayCollection([$existingItem]));

        $this->orderItemReorderValidator->expects($this->once())
            ->method('validate')
            ->with($orderItem);

        $this->quantityModifier->expects($this->once())
            ->method('modify')
            ->with($existingItem, 5); // 3 + 2

        $this->orderProcessor->expects($this->once())
            ->method('process')
            ->with($order);

        $this->orderRepository->expects($this->once())
            ->method('add')
            ->with($order);

        $this->reorderService->reorder($orderItem);
    }

    public function testReorderFailsWhenValidatorThrowsException(): void
    {
        $orderItem = $this->createMock(OrderItemInterface::class);

        $this->orderItemReorderValidator->method('validate')
            ->willThrowException(new ReorderException('Validation error'));

        $this->expectException(ReorderException::class);
        $this->expectExceptionMessage('Validation error');

        $this->reorderService->reorder($orderItem);
    }

    public function testReorderFailsWhenVariantIsNull(): void
    {
        $orderItem = $this->createMock(OrderItemInterface::class);

        $orderItem->method('getVariant')->willReturn(null);

        $this->expectException(ReorderException::class);
        $this->expectExceptionMessage('Product variant not available.');

        $this->reorderService->reorder($orderItem);
    }

    public function testReorderFailsWhenQuantityIsZero(): void
    {
        $orderItem = $this->createMock(OrderItemInterface::class);
        $variant = $this->createMock(ProductVariantInterface::class);

        $orderItem->method('getVariant')->willReturn($variant);
        $orderItem->method('getQuantity')->willReturn(0);

        $this->expectException(ReorderException::class);
        $this->expectExceptionMessage('Quantity must be greater than 0.');

        $this->reorderService->reorder($orderItem);
    }
}
