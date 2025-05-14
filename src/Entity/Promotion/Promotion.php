<?php

declare(strict_types=1);

namespace App\Entity\Promotion;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Promotion as BasePromotion;
use Sylius\Component\Promotion\Model\PromotionTranslationInterface;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_promotion')]
class Promotion extends BasePromotion
{
    protected function createTranslation(): PromotionTranslationInterface
    {
        return new PromotionTranslation();
    }
}
