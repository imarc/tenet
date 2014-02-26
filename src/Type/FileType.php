<?php
namespace Tenet\Type;

use SplFileInfo;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * A file data type for doctrine DBs
 */
class FileType extends StringType
{
    const FILE = 'file';

    /**
     *
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value
            ? new SplFileInfo($value)
            : NULL;
    }

    /**
     *
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value
            ? $value->getPath()
            : NULL;
    }

    /**
     *
     */
    public function getName()
    {
        return self::FILE;
    }
}
