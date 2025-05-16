<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);


namespace App\Plugin\Installer;

interface PluginInstallerInterface
{
    public function supports(string $packageName): bool;

    public function install(string $version, $input, $output): void;
}
