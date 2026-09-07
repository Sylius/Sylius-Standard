<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Updater;

use Sylius\Component\Order\Model\OrderInterface;
use SyliusOrderNotePlugin\Entity\OrderNoteAwareInterface;

interface OrderNoteUpdaterInterface
{
    public function update(
        OrderInterface&OrderNoteAwareInterface $order,
        ?string $content,
    ): void;
}
