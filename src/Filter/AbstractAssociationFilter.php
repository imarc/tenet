<?php
namespace Tenet\Filter;

abstract class AbstractAssociationFilter
{
	/**
	 * @todo refactor this... it can be better for scalar vs array
	 * @todo rename the object var in the checks
	 */
	protected function makeObject($manager, $object, $target, $data)
	{
		$metadata    = $manager->getClassMetadata($target);
		$identifiers = $metadata->getIdentifierFieldNames($object);

		// handle object of type target
		if ($data instanceof $target) {
			return $data;
		}

		// handle scalar identifier
		if (is_scalar($data) && count($identifiers) === 1) {
			$object = $this->manager->find($target, $data) ?: new $target;
			return $this->fill($object, $values);
		}

		// handle keyed identifier(s)
		if (is_array($data)) {
			// get array of identifier data passed in
			$ids = array_filter(array_intersect_key($data, array_flip($identifiers)), function($v) {
				return ($v === '' || $v === null) ? false : true;
			});

			// if all identifiers are present, try to find the object
			if (count($ids) === count($identifiers)) {
				$object = $this->manager->find($target, $data) ?: new $target;
				return $this->fill($object, $values);
			} 
		}

		return new $target;
	}
}
