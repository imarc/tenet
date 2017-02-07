<?php
namespace Tenet\Filter;

abstract class AbstractAssociationFilter
{
	/**
	 * @todo refactor this... it can be better for scalar vs array
	 * @todo rename the object var in the checks
	 */
	protected function makeObject($accessor, $object, $field, $data)
	{
		$class          = get_class($object);
		$manager        = $accessor->getObjectManager($class);
		$objectMetadata = $manager->getClassMetadata($class);
		$target         = $objectMetadata->getAssociationTargetClass($field);
		$targetMetadata = $manager->getClassMetadata($target);
		$identifiers    = $targetMetadata->getIdentifierFieldNames();

		if ($data === null || $data === '') {
			$targetObject = null;

		} elseif ($data instanceof $target) {
			// handle object of type target
			$targetObject = $data;

		} elseif (is_scalar($data) && $data !== '' && count($identifiers) === 1) {
			// handle scalar identifier
			$targetObject = $manager->find($target, $data) ?: null;

		} else {
			// handle compound identifiers
			$targetObject = new $target();

			if (is_array($data)) {
				// get array of identifier data passed in
				$ids = array_filter(array_intersect_key($data, array_flip($identifiers)), function($v) {
					return ($v === '' || $v === null) ? false : true;
				});

				// if all identifiers are present, try to find the object
				if (count($ids) === count($identifiers)) {
					if ($existing_record = $manager->find($target, $ids)) {
						$targetObject = $existing_record;
					}
				}

				$accessor->fill($targetObject, $data);
			}
		}

		return $targetObject;
	}
}
