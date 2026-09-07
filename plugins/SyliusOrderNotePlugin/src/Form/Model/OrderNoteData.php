<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Form\Model;

final class OrderNoteData
{
    public function __construct(public ?string $note = null)
    {
    }
}
