<?php

namespace Tenet;

use InvalidArgumentException;

/**
 *
 */
trait GettableTrait
{
	/**
	 *
	 */
	public function generateGetterCallable($field)
	{
		$callable = [$this, 'get' . ucfirst($field)];

		if (!is_callable($callable)) {
			$callable = function() use ($field) {
				return $this->get($field);
			};
		}

		return $callable;
	}


	/**
	 *
	 */
	public function get($field)
	{
		if (!property_exists($this, $field)) {
			throw new InvalidArgumentException(sprintf("%s has no property %s", get_class($this), $field));
		}

		return $this->$field;
	}
}
