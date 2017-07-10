<?php

namespace Tenet;

/**
 *
 */
class BooleanFilter implements FilterInterface
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
		if ($value === '' || $value === 'null') {
			return NULL;
		} elseif ($value === 'true') {
			return TRUE;
		} elseif ($value === 'false') {
			return FALSE;
		}

		return (bool) $value;
	}
}
