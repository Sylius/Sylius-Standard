<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Entity;

use DateTimeImmutable;
use Override;
use Sylius\Component\Order\Model\OrderInterface;

class OrderNote implements OrderNoteInterface
{
    protected ?int $id = null;

    protected ?OrderInterface $order = null;

    protected ?string $note = null;

    protected DateTimeImmutable $createdAt;

    protected ?DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    #[Override]
    public function getId(): ?int
    {
        return $this->id;
    }

    #[Override]
    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    #[Override]
    public function setOrder(?OrderInterface $order): void
    {
        $this->order = $order;
    }

    #[Override]
    public function getNote(): ?string
    {
        return $this->note;
    }

    #[Override]
    public function setNote(?string $note): void
    {
        $this->note = $note;
    }

    #[Override]
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[Override]
    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    #[Override]
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    #[Override]
    public function setUpdatedAt(?DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
