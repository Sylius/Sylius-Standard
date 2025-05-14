<?php

declare(strict_types=1);

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Payment\Model\PaymentRequest as BasePaymentRequest;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_payment_request')]
class PaymentRequest extends BasePaymentRequest
{
}
