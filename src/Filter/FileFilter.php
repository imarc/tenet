<?php
namespace Tenet\Filter;

use SplFileInfo;
use Tenet\FilterInterface;
use Tenet\Accessor;

class FileFilter implements FilterInterface
{
	public function convertToGetterValue(Accessor $accessor, $object, $field, $value)
	{
		return $value;
	}

	public function convertToSetterValue(Accessor $accessor, $object, $field, $value)
	{
		if ($value instanceof SplFileInfo) {
			return $value;
		} else if ('' === $value) {
			return NULL;
		} else if (is_string($value)) {
			$value = new SplFileInfo($value);
		} else if (is_object($value) && !($value instanceof SplFileInfo)) {
			$value = new SplFileInfo((string) $value);
		}

		return $value;
	}
}

