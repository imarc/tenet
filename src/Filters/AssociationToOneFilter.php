<?php

namespace Tenet;

/**
 *
 */
class AssociationToOneFilter extends AbstractAssociationFilter implements FilterInterface
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
		return $this->makeObject($accessor, $object, $field, $value);
	}
}
