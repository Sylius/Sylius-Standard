<?php

declare(strict_types=1);

namespace App\Repository;

use Sylius\Bundle\CoreBundle\Doctrine\ORM\PaymentRepository as BasePaymentRepository;
use Sylius\MarketplaceSuite\Component\Order\Repository\PaymentRepositoryInterface;
use Sylius\MarketplaceSuite\Component\Order\Repository\PaymentRepositoryTrait;

class PaymentRepository extends BasePaymentRepository implements PaymentRepositoryInterface
{
    use PaymentRepositoryTrait;
}
