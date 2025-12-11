<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Order\Order;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    public function testAdminNotesCanBeSetAndRetrieved(): void
    {
        $note = 'This is an admin note';

        $order = $this->createOrderWithAdminNote($note);

        $this->assertEquals($note, $order->getAdminNotes());
    }

    public function testAdminNotesCanBeNull(): void
    {
        $order = $this->createOrderWithAdminNote(null);

        $this->assertNull($order->getAdminNotes());
    }

    public function testAdminNotesAreTruncatedTo500Chars(): void
    {
        $longNote = str_repeat('a', 600);

        $order = $this->createOrderWithAdminNote($longNote);

        $expected = mb_substr($longNote, 0, Order::MAX_NOTE_LENGTH);
        $this->assertEquals(Order::MAX_NOTE_LENGTH, mb_strlen($order->getAdminNotes()));
        $this->assertEquals($expected, $order->getAdminNotes());
    }

    public function testAdminNotesTrimsWhitespace(): void
    {
        $noteWithWhitespace = '  test note  ';

        $order = $this->createOrderWithAdminNote($noteWithWhitespace);

        $this->assertEquals('test note', $order->getAdminNotes());
    }

    public function testAdminNotesEmptyStringBecomesNull(): void
    {
        $order = $this->createOrderWithAdminNote('   ');

        $this->assertNull($order->getAdminNotes());
    }

    private function createOrderWithAdminNote(?string $note = null): Order
    {
        $order = new Order();
        $order->setAdminNotes($note);

        return $order;
    }
}
