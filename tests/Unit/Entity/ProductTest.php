<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Product\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function testSetAndGetColor(): void
    {
        $product = new Product();
        $product->setColor('red');

        $this->assertSame('red', $product->getColor());
    }

    public function testOptionalColorField()
    {
        $product = new Product();

        $this->assertNull($product->getColor());
    }
}
