<?php

declare(strict_types=1);

namespace App\Entity\Order\NoteField;

interface OrderInterface
{
    public function getNote(): ?string;

    public function setNote(?string $note): void;
}
