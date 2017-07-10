<?php

namespace Tenet;

use DateTime;

/**
 *
 */
class DateTimeFilter implements FilterInterface
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
		if (!$value instanceof DateTime) {
			if ('' === $value) {
				return NULL;

			} elseif (is_int($value)) {
				$timestamp = $value;
				$value     = new DateTime();

				$value->setTimestamp($timestamp);

			} else {
				$value = new DateTime((string) $value);
			}
		}

		return $value;
	}
}
