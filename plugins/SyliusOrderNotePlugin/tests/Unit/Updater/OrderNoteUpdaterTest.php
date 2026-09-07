<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Tests\Unit\Updater;

use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Sylius\Resource\Factory\FactoryInterface;
use SyliusOrderNotePlugin\Entity\OrderNote;
use SyliusOrderNotePlugin\Tests\Application\Entity\Order;
use SyliusOrderNotePlugin\Updater\OrderNoteUpdater;

final class OrderNoteUpdaterTest extends TestCase
{
    public function testCreatesTrimmedNoteUsingResourceFactory(): void
    {
        $order = new Order();
        $note = new OrderNote();
        $factory = $this->createMock(FactoryInterface::class);
        $factory->expects(self::once())->method('createNew')->willReturn($note);
        $manager = $this->createMock(ObjectManager::class);
        $manager->expects(self::once())->method('persist')->with($note);
        $manager->expects(self::once())->method('flush');

        (new OrderNoteUpdater($factory, $manager))->update($order, '  Fragile parcel  ');

        self::assertSame('Fragile parcel', $note->getNote());
        self::assertSame($order, $note->getOrder());
        self::assertSame($note, $order->getOrderNote());
        self::assertNotNull($note->getUpdatedAt());
    }

    public function testUpdatesExistingNoteWithoutReplacingIt(): void
    {
        $order = new Order();
        $note = new OrderNote();
        $createdAt = $note->getCreatedAt();
        $order->setOrderNote($note);
        $factory = $this->createMock(FactoryInterface::class);
        $factory->expects(self::never())->method('createNew');
        $manager = $this->createMock(ObjectManager::class);
        $manager->expects(self::once())->method('persist')->with($note);
        $manager->expects(self::once())->method('flush');

        (new OrderNoteUpdater($factory, $manager))->update($order, 'Updated');

        self::assertSame($note, $order->getOrderNote());
        self::assertSame('Updated', $note->getNote());
        self::assertSame($createdAt, $note->getCreatedAt());
    }

    public function testWhitespaceDeletesExistingNote(): void
    {
        $order = new Order();
        $note = new OrderNote();
        $order->setOrderNote($note);
        $factory = $this->createMock(FactoryInterface::class);
        $factory->expects(self::never())->method('createNew');
        $manager = $this->createMock(ObjectManager::class);
        $manager->expects(self::once())->method('remove')->with($note);
        $manager->expects(self::never())->method('persist');
        $manager->expects(self::once())->method('flush');

        (new OrderNoteUpdater($factory, $manager))->update($order, '   ');

        self::assertNull($order->getOrderNote());
    }

    public function testEmptyContentDoesNotCreateNote(): void
    {
        $order = new Order();
        $factory = $this->createMock(FactoryInterface::class);
        $factory->expects(self::never())->method('createNew');
        $manager = $this->createMock(ObjectManager::class);
        $manager->expects(self::never())->method('persist');
        $manager->expects(self::never())->method('remove');

        (new OrderNoteUpdater($factory, $manager))->update($order, null);

        self::assertNull($order->getOrderNote());
    }
}
