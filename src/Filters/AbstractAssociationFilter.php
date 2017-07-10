<?php

namespace Tenet;

/**
 *
 */
abstract class AbstractAssociationFilter
{
	/**
	 *
	 */
	protected function makeObject(Accessor $accessor, $object, $field, $data)
	{
		$class          = get_class($object);
		$manager        = $accessor->getObjectManager($class);
		$objectMetadata = $manager->getClassMetadata($class);
		$target         = $objectMetadata->getAssociationTargetClass($field);
		$targetMetadata = $manager->getClassMetadata($target);
		$identifiers    = $targetMetadata->getIdentifierFieldNames();

		if ($data === NULL || $data === '') {
			$targetObject = NULL;

		} elseif ($data instanceof $target) {
			$targetObject = $data;

		} elseif (is_scalar($data) && count($identifiers) === 1) {
			$targetObject = $manager->find($target, $data);

		} else {
			$targetObject = new $target();

			if (is_array($data)) {
				$ids = array_filter(array_intersect_key($data, array_flip($identifiers)), function($v) {
					return !($v === '' || $v === NULL);
				});

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
