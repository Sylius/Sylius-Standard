<?php

declare(strict_types=1);

namespace SyliusOrderNotePlugin;

use Override;
use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SyliusOrderNotePlugin extends Bundle
{
    use SyliusPluginTrait;

    #[Override]
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
