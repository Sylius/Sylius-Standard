<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Order\Model\OrderInterface;

trait OrderNoteAwareTrait
{
    #[ORM\OneToOne(targetEntity: OrderNoteInterface::class, mappedBy: 'order', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected ?OrderNoteInterface $orderNote = null;

    public function getOrderNote(): ?OrderNoteInterface
    {
        return $this->orderNote;
    }

    public function setOrderNote(?OrderNoteInterface $orderNote): void
    {
        $this->orderNote = $orderNote;

        if ($this instanceof OrderInterface && null !== $orderNote && $orderNote->getOrder() !== $this) {
            $orderNote->setOrder($this);
        }
    }
}
