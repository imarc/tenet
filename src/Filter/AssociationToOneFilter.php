<?php
namespace Tenet\Filter;

use Tenet\FilterInterface;
use Tenet\Accessor;

class AssociationToOneFilter extends AbstractAssociationFilter implements FilterInterface
{
	public function convertToSetterValue(Accessor $accessor, $object, $field, $value)
	{
		return $this->makeObject($accessor, $object, $field, $value);
	}

	public function convertToGetterValue(Accessor $accessor, $object, $field, $value)
	{
		return $value;
	}
}
