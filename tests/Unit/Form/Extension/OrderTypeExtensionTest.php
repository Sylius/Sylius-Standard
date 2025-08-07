<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\Extension;

use App\Form\Extension\OrderTypeExtension;
use Sylius\Bundle\OrderBundle\Form\Type\OrderType;
use Symfony\Component\Form\Test\TypeTestCase;

class OrderTypeExtensionTest extends TypeTestCase
{
    public function test_get_extended_types(): void
    {
        $extension = new OrderTypeExtension();
        $types = iterator_to_array($extension::getExtendedTypes());

        self::assertContains(OrderType::class, $types);
    }
}
