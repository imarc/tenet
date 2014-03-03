<?php
namespace Tenet\Access;

use Tenet\Accessor;

trait ProtectedTrait
{
	public function loadFillableFields(Accessor $accessor)
	{
		return get_object_vars($this);
	}
}
