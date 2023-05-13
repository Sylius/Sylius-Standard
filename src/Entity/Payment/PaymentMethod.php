<?php

declare(strict_types=1);

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ImageAwareInterface;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Core\Model\PaymentMethod as BasePaymentMethod;
use Sylius\Component\Payment\Model\PaymentMethodTranslationInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_payment_method")
 */
#[ORM\Entity]
#[ORM\Table(name: 'sylius_payment_method')]
class PaymentMethod extends BasePaymentMethod implements ImageAwareInterface
{
    /**
     * @Assert\Valid
     * @ORM\OneToOne(mappedBy="owner", targetEntity="App\Entity\Payment\PaymentMethodImage", cascade={"all"}, orphanRemoval=true)
     */
    #[Assert\Valid]
    #[ORM\OneToOne(mappedBy: 'owner', targetEntity: PaymentMethodImage::class, cascade: ['all'], orphanRemoval: true)]
    protected ?PaymentMethodImage $image = null;

    /** @return PaymentMethodImage|null */
    public function getImage(): ?ImageInterface
    {
        return $this->image;
    }

    /** @var PaymentMethodImage|null $image */
    public function setImage(?ImageInterface $image): void
    {
        $image?->setOwner($this);

        $this->image = $image;
    }

    protected function createTranslation(): PaymentMethodTranslationInterface
    {
        return new PaymentMethodTranslation();
    }
}
