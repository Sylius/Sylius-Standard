<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Tests\Unit\Entity;

use App\Entity\Order\Order;
use PHPUnit\Framework\TestCase;
use SyliusOrderNotePlugin\Entity\OrderNote;

final class OrderNoteAwareTraitTest extends TestCase
{
    private Order $order;

    protected function setUp(): void
    {
        $this->order = new Order();
    }

    public function testInitialOrderNoteIsNull(): void
    {
        $this->assertNull($this->order->getOrderNote());
    }

    public function testSetAndGetOrderNote(): void
    {
        $orderNote = new OrderNote();
        $orderNote->setNote('Notatka do zamówienia');

        $this->order->setOrderNote($orderNote);

        $this->assertSame($orderNote, $this->order->getOrderNote());
        $this->assertSame($this->order, $orderNote->getOrder());
    }

    public function testClearOrderNote(): void
    {
        $orderNote = new OrderNote();
        $this->order->setOrderNote($orderNote);
        $this->assertNotNull($this->order->getOrderNote());

        $this->order->setOrderNote(null);
        $this->assertNull($this->order->getOrderNote());
    }
}
