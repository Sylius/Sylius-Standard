<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Entity;

interface OrderNoteAwareInterface
{
    public function getOrderNote(): ?OrderNoteInterface;

    public function setOrderNote(?OrderNoteInterface $orderNote): void;
}
