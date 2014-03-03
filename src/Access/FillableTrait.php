<?php
namespace Tenet\Access;

use Tenet\Accessor;

trait FillableTrait
{
	public function loadFillableFields(Accessor $accessor)
	{
		return array_keys(get_object_vars($this));
	}
}
