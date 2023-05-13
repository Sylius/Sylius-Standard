<?php

declare(strict_types=1);

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Image;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_payment_method_image")
 *
 * @method PaymentMethod|null getOwner()
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_payment_method_image')]
class PaymentMethodImage extends Image
{
    #[Assert\Image(groups: ['sylius'])]
    protected $file;

    /**
     * @ORM\OneToOne(inversedBy="image", targetEntity="App\Entity\Payment\PaymentMethod")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @var PaymentMethod|null
     */
    #[ORM\OneToOne(inversedBy: 'image', targetEntity: PaymentMethod::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected $owner;

    public function __construct()
    {
        $this->type = 'default';
    }

    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->getOwner();
    }

    public function setPaymentMethod(?PaymentMethod $paymentMethod): void
    {
        $this->setOwner($paymentMethod);
    }
}
