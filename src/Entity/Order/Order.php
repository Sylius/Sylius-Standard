<?php

declare(strict_types=1);

namespace App\Entity\Order;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Order as BaseOrder;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_order')]
class Order extends BaseOrder {
    #[ORM\Column(type: "string", nullable: true, length: 500)]
    private ?string $note = null;

    public function getNote(): ?string {
        return $this->note;
    }

    public function setNote(?string $note): void {
        $this->note = $note;
    }
}
