<?php

declare(strict_types=1);

namespace App\Entity\Order;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Order as BaseOrder;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_order')]
class Order extends BaseOrder
{
    #[ORM\Column(type: Types::TEXT, length: 500, nullable: true)]
    private ?string $adminnotes;

    public function getAdminnotes(): ?string
    {
        return $this->adminnotes;
    }

    public function setAdminnotes(string $adminnotes): void
    {
        $this->adminnotes = $adminnotes;
    }
}

