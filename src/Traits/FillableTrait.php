<?php

namespace Tenet;

/**
 *
 */
trait FillableTrait
{
	/**
	 *
	 */
	public function listAccessibleFields(Accessor $accessor)
	{
		return array_keys(get_object_vars($this));
	}
}
