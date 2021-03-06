<?php
namespace Tenet\Filter;

use Tenet\FilterInterface;
use Tenet\Accessor;

class DateTimeFilter implements FilterInterface
{
	public function convertToSetterValue(Accessor $accessor, $object, $field, $value)
	{
		if ($value instanceof \DateTime) {
			return $value;
		} else if ('' === $value) {
			return null;
		} else if (is_int($value)) {
			$dateTime = new \DateTime();
			$dateTime->setTimestamp($value);
			$value = $dateTime;
		} else if (is_string($value)) {
			$value = new \DateTime($value);
		}

		return $value;
	}

	public function convertToGetterValue(Accessor $accessor, $object, $field, $value)
	{
		return $value;
	}
}

