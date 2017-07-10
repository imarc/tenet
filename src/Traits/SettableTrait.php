<?php

namespace Tenet;

use InvalidArgumentException;

/**
 *
 */
trait SettableTrait
{
	/**
	 *
	 */
	public function generateSetterCallable($field)
	{
		$callable = [$this, 'set' . ucfirst($field)];

		if (!is_callable($callable)) {
			$callable = function($value) use ($field) {
				$this->set($field, $value);
			};
		}

		return $callable;
	}


	/**
	 *
	 */
	public function set($field, $value)
	{
		if (!property_exists($this, $field)) {
			throw new InvalidArgumentException(sprintf("%s has no property %s", get_class($this), $field));
		}

		$this->$field = $value;

		return $this;
	}
}
