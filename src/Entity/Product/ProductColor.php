<?php

namespace App\Entity\Product;

use Exception;

class ProductColor
{
    const RED = 'red';
    const BLUE = 'blue';
    const GREEN = 'green';

    /**
     * @var string
     */
    protected $color;

    public function __construct(string $color)
    {
        if(null == $color) {
            return;
        }

        if(false === in_array($color, [self::RED, self::BLUE, self::GREEN])) {
            throw new Exception("Invalid color!");
        }

        $this->color = $color;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function __toString()
    {
        return $this->color;
    }

    public static function getColors(): array
    {
        return [
            self::RED,
            self::GREEN,
            self::BLUE,
        ];
    }
}
