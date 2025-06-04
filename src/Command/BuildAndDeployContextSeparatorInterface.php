<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Command;

/** This interface is intended to mimic Platform.sh's Build and Deploy separation.*/
interface BuildAndDeployContextSeparatorInterface
{
    public function build(string $store): void;

    public function deploy(?string $store = null): void;
}
