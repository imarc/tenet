<?php
namespace Tenet\Filter;

use Tenet\FilterInterface;
use Tenet\Accessor;

class BooleanFilter implements FilterInterface
{
	public function convertToSetterValue(Accessor $accessor, $object, $field, $value)
	{
		if ($value === '' || $value === 'null') {
			return NULL;
		} elseif ($value === 'true') {
			return TRUE;
		} elseif ($value === 'false') {
			return FALSE;
		}

		return (bool) $value;
	}

	public function convertToGetterValue(Accessor $accessor, $object, $field, $value)
	{
		return $value;
	}
}
