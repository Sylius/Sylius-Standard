<?php

declare(strict_types=1);

namespace App\Entity\Promotion;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Promotion\Model\PromotionTranslation as BasePromotionTranslation;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_promotion_translation')]
class PromotionTranslation extends BasePromotionTranslation
{
}
