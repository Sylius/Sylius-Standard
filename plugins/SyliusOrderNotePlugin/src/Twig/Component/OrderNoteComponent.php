<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Twig\Component;

use Sylius\Component\Order\Model\OrderInterface;
use Sylius\TwigHooks\Hookable\Metadata\HookableMetadata;
use SyliusOrderNotePlugin\Entity\OrderNoteAwareInterface;
use SyliusOrderNotePlugin\Form\Factory\OrderNoteFormFactoryInterface;
use Symfony\Component\Form\FormView;
use Webmozart\Assert\Assert;

final class OrderNoteComponent
{
    public ?HookableMetadata $hookableMetadata = null;

    public (OrderInterface&OrderNoteAwareInterface)|null $order = null;

    public function __construct(private readonly OrderNoteFormFactoryInterface $formFactory)
    {
    }

    public function getForm(): FormView
    {
        Assert::notNull($this->order, 'The order must be provided when mounting the note component.');

        return $this->formFactory->create($this->order)->createView();
    }
}
