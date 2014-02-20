<?php
namespace Tenet\Type;

use SplFileInfo;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * A file data type for doctrine DBs
 */
class FileType extends Type
{
    const FILE = 'file';

    /**
     *
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return new SplFileInfo($value);
    }

    /**
     *
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value->getPath();
    }

    /**
     *
     */
    public function getName()
    {
        return self::FILE;
    }

    /**
     *
     */
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'VARCHAR';
    }
}