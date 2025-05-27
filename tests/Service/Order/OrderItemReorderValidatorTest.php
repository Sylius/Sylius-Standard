<?php
declare(strict_types=1);

namespace App\Tests\Service\Order;

use App\Exception\ReorderException;
use App\Service\Order\OrderItemReorderValidator;
use App\Service\Order\OrderItemReorderValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Inventory\Checker\OrderItemAvailabilityCheckerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ShopUserInterface;
use Symfony\Bundle\SecurityBundle\Security;

class OrderItemReorderValidatorTest extends TestCase
{
    private OrderItemReorderValidator $validator;
    private MockObject $security;
    private MockObject $availabilityChecker;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);
        $this->availabilityChecker = $this->createMock(OrderItemAvailabilityCheckerInterface::class);

        $this->validator = new OrderItemReorderValidator(
            $this->security,
            $this->availabilityChecker
        );
    }

    public function testValidateSuccessful(): void
    {
        $user = $this->createMock(ShopUserInterface::class);
        $customer = $this->createMock(CustomerInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $orderItem = $this->createMock(OrderItemInterface::class);

        $this->security->method('getUser')->willReturn($user);
        $orderItem->method('getOrder')->willReturn($order);
        $order->method('getCustomer')->willReturn($customer);
        $customer->method('getUser')->willReturn($user);
        $this->availabilityChecker->method('isReservedStockSufficient')
            ->with($orderItem)
            ->willReturn(true);

        $this->validator->validate($orderItem);
        $this->assertTrue(true);
    }

    public function testValidateFailsWhenUserDoesNotMatch(): void
    {
        $currentUser = $this->createMock(ShopUserInterface::class);
        $orderUser = $this->createMock(ShopUserInterface::class);
        $customer = $this->createMock(CustomerInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $orderItem = $this->createMock(OrderItemInterface::class);

        $this->security->method('getUser')->willReturn($currentUser);
        $orderItem->method('getOrder')->willReturn($order);
        $order->method('getCustomer')->willReturn($customer);
        $customer->method('getUser')->willReturn($orderUser); // Different user

        $this->expectException(ReorderException::class);
        $this->expectExceptionMessage('You are not allowed to reorder this item.');

        $this->validator->validate($orderItem);
    }

    public function testValidateFailsWhenNoCurrentUser(): void
    {
        $orderItem = $this->createMock(OrderItemInterface::class);
        $this->security->method('getUser')->willReturn(null);

        $this->expectException(ReorderException::class);
        $this->expectExceptionMessage('You are not allowed to reorder this item.');

        $this->validator->validate($orderItem);
    }

    public function testValidateFailsWhenOrderIsNull(): void
    {
        $user = $this->createMock(ShopUserInterface::class);
        $orderItem = $this->createMock(OrderItemInterface::class);

        $this->security->method('getUser')->willReturn($user);
        $orderItem->method('getOrder')->willReturn(null);

        $this->expectException(ReorderException::class);
        $this->expectExceptionMessage('You are not allowed to reorder this item.');

        $this->validator->validate($orderItem);
    }

    public function testValidateFailsWhenCustomerIsNull(): void
    {
        $user = $this->createMock(ShopUserInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $orderItem = $this->createMock(OrderItemInterface::class);

        $this->security->method('getUser')->willReturn($user);
        $orderItem->method('getOrder')->willReturn($order);
        $order->method('getCustomer')->willReturn(null);

        $this->expectException(ReorderException::class);
        $this->expectExceptionMessage('You are not allowed to reorder this item.');

        $this->validator->validate($orderItem);
    }

    public function testValidateFailsWhenStockIsInsufficient(): void
    {
        $user = $this->createMock(ShopUserInterface::class);
        $customer = $this->createMock(CustomerInterface::class);
        $order = $this->createMock(OrderInterface::class);
        $orderItem = $this->createMock(OrderItemInterface::class);

        $this->security->method('getUser')->willReturn($user);
        $orderItem->method('getOrder')->willReturn($order);
        $order->method('getCustomer')->willReturn($customer);
        $customer->method('getUser')->willReturn($user);

        $this->availabilityChecker->method('isReservedStockSufficient')
            ->with($orderItem)
            ->willReturn(false);

        $this->expectException(ReorderException::class);
        $this->expectExceptionMessage('The requested quantity is no longer available.');

        $this->validator->validate($orderItem);
    }
}
