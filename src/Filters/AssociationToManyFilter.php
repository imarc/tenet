<?php

namespace Tenet;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 *
 */
class AssociationToManyFilter extends AbstractAssociationFilter implements FilterInterface
{
	/**
	 *
	 */
	public function convertToGetterValue(Accessor $accessor, $object, $field, $value)
	{
		if ($value instanceof Collection) {
			return $value;
		}

		return new ArrayCollection();
	}


	/**
	 *
	 */
	public function convertToSetterValue(Accessor $accessor, $object, $field, $value)
	{
		$incomingCollection = new ArrayCollection();

		if ($value) {
			if (!is_array($value)) {
				$values = array($value);
			} else {
				$values = $value;
			}

			foreach ($values as $key => $value) {
				$incomingCollection->add($this->makeObject($accessor, $object, $field, $value));
			}
		}

		return $incomingCollection;
	}
}
