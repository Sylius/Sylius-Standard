<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin\Entity;

use DateTimeImmutable;
use Override;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

interface OrderNoteInterface extends ResourceInterface
{
    public const int MAX_LENGTH = 500;

    #[Override]
    public function getId(): ?int;

    public function getOrder(): ?OrderInterface;

    public function setOrder(?OrderInterface $order): void;

    public function getNote(): ?string;

    public function setNote(?string $note): void;

    public function getCreatedAt(): DateTimeImmutable;

    public function setCreatedAt(DateTimeImmutable $createdAt): void;

    public function getUpdatedAt(): ?DateTimeImmutable;

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): void;
}
