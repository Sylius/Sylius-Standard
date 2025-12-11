<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context\Order;

use Behat\Behat\Context\Context;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Webmozart\Assert\Assert;

final class OrderSetupContext implements Context
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
    }

    /**
     * @Given the order :orderNumber has admin notes :note
     */
    public function theOrderHasAdminNotes(string $orderNumber, string $note): void
    {
        $order = $this->findOrderByNumber($orderNumber);
        $order->setAdminNotes($note);
        $this->orderRepository->add($order);
    }

    private function findOrderByNumber(string $orderNumber): OrderInterface
    {
        $orderNumber = ltrim($orderNumber, '#');
        $order = $this->orderRepository->findOneByNumber($orderNumber);
        Assert::notNull($order, sprintf('Order with number "%s" not found', $orderNumber));

        return $order;
    }
}
