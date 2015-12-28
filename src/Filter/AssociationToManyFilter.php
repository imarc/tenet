<?php
namespace Tenet\Filter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\Debug;
use Tenet\FilterInterface;
use Tenet\Accessor;

class AssociationToManyFilter extends AbstractAssociationFilter implements FilterInterface
{
	/**
	 *
	 */
	public function convertToSetterValue(Accessor $accessor, $object, $field, $value)
	{
		$manager            = $accessor->getObjectManager();
		$objectMetadata     = $manager->getClassMetadata(get_class($object));
		$collection         = $accessor->get($object, $field);
		$incomingCollection = new ArrayCollection();

		// very helpful:
		// http://doctrine-orm.readthedocs.org/en/latest/reference/unitofwork-associations.html

		$mappedField = $objectMetadata->getAssociationMappedByTargetField($field);
		$mappedClass = $objectMetadata->getAssociationTargetClass($field);

		if ($value) {
			$values = !is_array($value)
				? array($value)
				: $value;

			foreach ($values as $key => $value) {
				$relatedObject = $this->makeObject($accessor, $object, $field, $value);

				if ($mappedField) {
					$inverse = $accessor->get($relatedObject, $mappedField);

					if ($inverse instanceof Collection) {
						//
						// Handle bi-directional
						//

						if (!$inverse->contains($object)) {
							$inverse->add($object);
						}

						//
						// Handle self referencing bi-directional
						//

						if ($mappedClass == get_class($object)) {
							$peer = $accessor->get($object, $mappedField);

							if (!$peer->contains($relatedObject)) {
								$peer->add($relatedObject);
							}
						}

					} else {

						//
						// If the inverse is not a collection, then we assume it's a one to many and we want to make
						// sure that teh related object's mapped field is set to this object.
						//

						$accessor->set($relatedObject, $mappedField, $object);
					}
				}

				$incomingCollection->add($relatedObject);
			}
		}

		foreach ($collection as $i => $relatedObject) {
			if (!$incomingCollection->contains($relatedObject)) {
				if ($mappedField) {
					$inverse = $accessor->get($relatedObject, $mappedField);

					if ($inverse instanceof Collection) {
						//
						// Handle bi-directional
						//

						if ($inverse->contains($object)) {
							$inverse->removeElement($object);
						}

						//
						// Handle self referencing bi-directional
						//

						if ($mappedClass == get_class($object)) {
							$peer = $accessor->get($object, $mappedField);

							if ($peer->contains($relatedObject)) {
								$peer->removeElement($relatedObject);
							}
						}

					} else {

						//
						// If the inverse is not a collection, then we assume it's a one to many and we want to make
						// sure that teh related object's mapped field is set to null.
						//

						$accessor->set($relatedObject, $mappedField, NULL);
					}
				}
			}
		}

		return $incomingCollection;
	}


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
}
