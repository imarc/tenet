<?php

namespace Tenet;

use DateTime;

/**
 *
 */
class IntegerFilter implements FilterInterface
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
		if ('' === $value) {
			return NULL;

		} elseif (is_numeric($value)) {
			return (int) $value;

		} else {
			return $value;
		}
	}
}
