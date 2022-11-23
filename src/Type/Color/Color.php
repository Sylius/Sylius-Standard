<?php
declare(strict_types=1);

namespace App\Type\Color;

use InvalidArgumentException;

class Color
{
    public const BLUE  = 1;
    public const RED   = 2;
    public const BLACK = 3;
    public const CONST_ARRAY
                       = [
            self::BLUE  => 'Blue',
            self::RED   => 'Red',
            self::BLACK => 'Black',
        ];

    private int $id;

    public function __construct(
        int $id
    ) {
        if (! array_key_exists($id, self::CONST_ARRAY)) {
            throw new InvalidArgumentException("Invalid color id!");
        }
        $this->id = $id;
    }

    public function __toString(): string
    {
        return self::CONST_ARRAY[$this->id];
    }

    public function getId(): int
    {
        return $this->id;
    }
}
