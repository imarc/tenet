<?php
namespace Tenet\Access;

use Tenet\Accessor;

trait SettableTrait
{
	public function generateSetterCallable(Accessor $accessor, $field)
	{
		return [$this, 'set' . ucfirst($field)];
	}

	public function set(Accessor $accessor, $field, $value)
	{
		if (!property_exists($this, $field)) {
			throw new InvalidArgumentException(sprintf("%s has no property %s", get_class($this), $field));
		}

		$this->$field = $accessor->convert($this, $field, $value, Accessor::SETTER);

		return $this;
	}
}
