<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Updater;

use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Override;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Resource\Factory\FactoryInterface;
use SyliusOrderNotePlugin\Entity\OrderNoteAwareInterface;
use SyliusOrderNotePlugin\Entity\OrderNoteInterface;

final class OrderNoteUpdater implements OrderNoteUpdaterInterface
{
    /**
     * @param FactoryInterface<OrderNoteInterface> $orderNoteFactory
     */
    public function __construct(
        private readonly FactoryInterface $orderNoteFactory,
        private readonly ObjectManager $objectManager,
    ) {
    }

    #[Override]
    public function update(
        OrderInterface&OrderNoteAwareInterface $order,
        ?string $content,
    ): void {
        $content = null === $content ? '' : trim($content);
        $note = $order->getOrderNote();

        if ('' === $content) {
            if (null !== $note) {
                $order->setOrderNote(null);
                $this->objectManager->remove($note);
            }

            $this->objectManager->flush();

            return;
        }

        $note ??= $this->orderNoteFactory->createNew();
        $note->setNote($content);
        $note->setUpdatedAt(new DateTimeImmutable());
        $order->setOrderNote($note);
        $this->objectManager->persist($note);
        $this->objectManager->flush();
    }
}
