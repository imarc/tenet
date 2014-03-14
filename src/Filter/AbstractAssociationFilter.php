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
		$manager        = $accessor->getObjectManager();
		$objectMetadata = $manager->getClassMetadata(get_class($object));
		$target         = $objectMetadata->getAssociationTargetClass($field);
		$targetMetadata = $manager->getClassMetadata($target);
		$identifiers    = $targetMetadata->getIdentifierFieldNames();

		if ($data === null || $data === '') {
			$targetObject = null;
		} else if ($data instanceof $target) {
			// handle object of type target
			$targetObject = $data;	
		} else if (is_scalar($data) && $data !== '' && count($identifiers) === 1) {
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

		if ($targetObject && $objectMetadata->isAssociationInverseSide($field)) {
			$owning = $objectMetadata->getAssociationMappedByTargetField($field);
			$accessor->set($targetObject, $owning, $object);
		}

		return $targetObject;
	}
}
