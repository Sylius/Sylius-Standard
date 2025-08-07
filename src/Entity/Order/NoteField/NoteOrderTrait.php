<?php

declare(strict_types=1);

namespace App\Entity\Order\NoteField;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait NoteOrderTrait
{
    #[ORM\Column(type: 'text', length: 500, nullable: true)]
    #[Assert\Length(
        max: 500,
        maxMessage: 'sylius.ui.note.valid.too_long',
        groups: ['sylius_shipping_address_update'],
    )]
    private ?string $note = null;

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): void
    {
        $this->note = $note;
    }
}
