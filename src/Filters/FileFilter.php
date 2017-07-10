<?php

namespace Tenet;

use SplFileInfo;

/**
 *
 */
class FileFilter implements FilterInterface
{
	/**
	 *
	 */
	public function convertToGetterValue(Accessor $accessor, $object, $field, $value)
	{
		return $value;
	}


	/**
	 *
	 */
	public function convertToSetterValue(Accessor $accessor, $object, $field, $value)
	{
		if ($value instanceof SplFileInfo) {
			return $value;
		}

		if ($value === NULL) {
			$value = $accessor->get($object, $field);
		} elseif (is_string($value)) {
			if (!$value) {
				$value = NULL;
			} else {
				$value = new SplFileInfo($value);
			}
		} else if (is_object($value) && is_callable([$value, '__toString'])) {
			$value = new SplFileInfo((string) $value);
		}

		return $value;
	}
}
