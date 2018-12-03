<?php

namespace ApiExtension\App\Type;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * My custom datatype.
 */
class EanType extends Type
{
    const EAN = 'ean';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return self::EAN;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return (string) trim($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return (string) trim($value);
    }

    public function getName()
    {
        return self::EAN;
    }
}
