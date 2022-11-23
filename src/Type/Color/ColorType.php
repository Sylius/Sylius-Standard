<?php
declare(strict_types=1);

namespace App\Type\Color;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class ColorType extends Type
{
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Color
    {
        if ($value === null) {
            return null;
        }

        return new Color((int)$value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof Color) {
            return $value->getId();
        }

        return null;
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'SMALLINT(1)';
    }

    protected function getDefaultDeclaration(): string
    {
        return 'SMALLINT(1)';
    }

    public function getName()
    {
        return "color_type";
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
