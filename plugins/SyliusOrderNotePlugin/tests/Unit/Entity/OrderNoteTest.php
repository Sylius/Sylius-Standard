<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Tests\Unit\Entity;

use App\Entity\Order\Order;
use PHPUnit\Framework\TestCase;
use SyliusOrderNotePlugin\Entity\OrderNote;
use SyliusOrderNotePlugin\Entity\OrderNoteInterface;

final class OrderNoteTest extends TestCase
{
    private OrderNoteInterface $orderNote;

    protected function setUp(): void
    {
        $this->orderNote = new OrderNote();
    }

    public function testInitialState(): void
    {
        $this->assertNull($this->orderNote->getId());
        $this->assertNull($this->orderNote->getOrder());
        $this->assertNull($this->orderNote->getNote());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->orderNote->getCreatedAt());
        $this->assertNull($this->orderNote->getUpdatedAt());
    }

    public function testSetAndGetOrder(): void
    {
        $order = new Order();
        $this->orderNote->setOrder($order);

        $this->assertSame($order, $this->orderNote->getOrder());
    }

    public function testSetAndGetNote(): void
    {
        $note = 'Ważna informacja o dostawie.';
        $this->orderNote->setNote($note);

        $this->assertSame($note, $this->orderNote->getNote());
    }

    public function testSetAndGetDates(): void
    {
        $createdAt = new \DateTimeImmutable('2026-01-01 10:00:00');
        $updatedAt = new \DateTimeImmutable('2026-01-02 12:00:00');

        $this->orderNote->setCreatedAt($createdAt);
        $this->orderNote->setUpdatedAt($updatedAt);

        $this->assertSame($createdAt, $this->orderNote->getCreatedAt());
        $this->assertSame($updatedAt, $this->orderNote->getUpdatedAt());
    }
}
