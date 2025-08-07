<?php

declare(strict_types=1);

namespace App\Entity\Order;

use App\Entity\Order\NoteField\NoteOrderTrait;
use App\Entity\Order\NoteField\OrderInterface as NoteFieldOrderInterface;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Order as BaseOrder;
use Sylius\MolliePlugin\Entity\AbandonedEmailOrderTrait;
use Sylius\MolliePlugin\Entity\MolliePaymentIdOrderTrait;
use Sylius\MolliePlugin\Entity\OrderInterface as MolliePluginOrderInterface;
use Sylius\MolliePlugin\Entity\QRCodeOrderTrait;
use Sylius\MolliePlugin\Entity\RecurringOrderTrait;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_order')]
class Order extends BaseOrder implements MolliePluginOrderInterface, NoteFieldOrderInterface
{
    use MolliePaymentIdOrderTrait;
    use QRCodeOrderTrait;
    use RecurringOrderTrait;
    use AbandonedEmailOrderTrait;
    use NoteOrderTrait;
}
