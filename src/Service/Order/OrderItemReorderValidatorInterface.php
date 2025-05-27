<?php
declare(strict_types=1);

namespace App\Service\Order;

use App\Exception\ReorderException;
use Sylius\Component\Core\Model\OrderItemInterface;

interface OrderItemReorderValidatorInterface
{
/**
* Validates whether an order item is allowed to be reordered by the current user.
*
* @throws ReorderException
*/
public function validate(OrderItemInterface $orderItem): void;
}
