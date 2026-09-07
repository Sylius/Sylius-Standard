<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Entity;

use Doctrine\ORM\Mapping as ORM;

trait OrderNoteAwareTrait
{
    #[ORM\OneToOne(targetEntity: OrderNote::class, mappedBy: 'order', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?OrderNoteInterface $orderNote = null;

    public function getOrderNote(): ?OrderNoteInterface
    {
        return $this->orderNote;
    }

    public function setOrderNote(?OrderNoteInterface $orderNote): void
    {
        $this->orderNote = $orderNote;

        if (null !== $orderNote && $orderNote->getOrder() !== $this) {
            $orderNote->setOrder($this);
        }
    }
}
