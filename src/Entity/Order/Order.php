<?php

declare(strict_types=1);

namespace App\Entity\Order;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Order as BaseOrder;
use Sylius\MolliePlugin\Entity\AbandonedEmailOrderTrait;
use Sylius\MolliePlugin\Entity\MolliePaymentIdOrderTrait;
use Sylius\MolliePlugin\Entity\OrderInterface;
use Sylius\MolliePlugin\Entity\QRCodeOrderTrait;
use Sylius\MolliePlugin\Entity\RecurringOrderTrait;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_order')]
class Order extends BaseOrder implements OrderInterface
{
    use MolliePaymentIdOrderTrait;
    use QRCodeOrderTrait;
    use RecurringOrderTrait;
    use AbandonedEmailOrderTrait;

    public const MAX_NOTE_LENGTH = 500;

    #[ORM\Column(name: 'admin_notes', type: 'string', length: self::MAX_NOTE_LENGTH, nullable: true)]
    protected ?string $adminNotes = null;

    public function getAdminNotes(): ?string
    {
        return $this->adminNotes;
    }

    public function setAdminNotes(?string $adminNotes): void
    {
        if ($adminNotes === null) {
            $this->adminNotes = null;

            return;
        }

        $trimmedNotes = trim($adminNotes);
        $this->adminNotes = $trimmedNotes === '' ? null : $this->truncateToMaxLength($trimmedNotes);
    }

    private function truncateToMaxLength(string $notes): string
    {
        return mb_substr($notes, 0, self::MAX_NOTE_LENGTH);
    }
}
