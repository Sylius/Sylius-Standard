<?php

declare(strict_types=1);

namespace App\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\AvatarImage as BaseAvatarImage;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_avatar_image')]
class AvatarImage extends BaseAvatarImage
{
}
