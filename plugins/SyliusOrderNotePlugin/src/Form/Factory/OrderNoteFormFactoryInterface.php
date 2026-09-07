<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Form\Factory;

use Sylius\Component\Order\Model\OrderInterface;
use SyliusOrderNotePlugin\Entity\OrderNoteAwareInterface;
use Symfony\Component\Form\FormInterface;

interface OrderNoteFormFactoryInterface
{
    public function create(OrderInterface&OrderNoteAwareInterface $order): FormInterface;
}
