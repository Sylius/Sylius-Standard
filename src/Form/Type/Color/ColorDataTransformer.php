<?php
declare(strict_types=1);

namespace App\Form\Type\Color;

use App\Type\Color\Color;
use Symfony\Component\Form\DataTransformerInterface;

class ColorDataTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        if ($value instanceof Color) {
            return $value->getId();
        }

        return $value;
    }

    public function reverseTransform($value)
    {
        if (is_int($value)) {
            return new Color((int)$value);
        }

        return null;
    }
}
