<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Form\Factory;

use Override;
use Sylius\Component\Order\Model\OrderInterface;
use SyliusOrderNotePlugin\Entity\OrderNoteAwareInterface;
use SyliusOrderNotePlugin\Form\Model\OrderNoteData;
use SyliusOrderNotePlugin\Form\Type\OrderNoteType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class OrderNoteFormFactory implements OrderNoteFormFactoryInterface
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Override]
    public function create(OrderInterface&OrderNoteAwareInterface $order): FormInterface
    {
        return $this->formFactory->create(
            OrderNoteType::class,
            new OrderNoteData($order->getOrderNote()?->getNote()),
            [
            'method' => 'POST',
            'action' => $this->urlGenerator->generate('sylius_order_note_admin_update', ['id' => $order->getId()]),
            ],
        );
    }
}
