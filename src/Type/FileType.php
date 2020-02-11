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

    private $baseDirectory;

    /**
     *
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (!$value) {
            return NULL;
        }

        return !preg_match('#^(/|\\\\|[a-z]:(\\\\|/)|\\\\|//)#i', $value)
            ? new SplFileInfo($this->baseDirectory . $value)
            : new SplFileInfo($value);
    }

    /**
     *
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!$value instanceof SplFileInfo) {
            return null;
        }

        if (strpos($value->getRealPath(), $this->baseDirectory) === 0) {
            return substr($value->getRealPath(), strlen($this->baseDirectory));
        }

        return $value->getRealPath();
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
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return TRUE;
    }


    /**
     *
     */
    public function setBaseDirectory($directory)
    {
        $this->baseDirectory = rtrim($directory, '/\\' . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
}
