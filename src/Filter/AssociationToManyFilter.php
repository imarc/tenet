<?php
namespace Tenet\Filter;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Util\Debug;
use Tenet\FilterInterface;
use Tenet\Accessor;

class AssociationToManyFilter extends AbstractAssociationFilter implements FilterInterface
{
	public function convertToSetterValue(Accessor $accessor, $object, $field, $value)
	{
		$manager            = $accessor->getObjectManager();
		$objectMetadata     = $manager->getClassMetadata(get_class($object));
		$collection         = $accessor->get($object, $field);
		$incomingCollection = new ArrayCollection();
		
		// very helpful: 
		// http://doctrine-orm.readthedocs.org/en/latest/reference/unitofwork-associations.html

		if ($value) {
			$values = !is_array($value)
				? array($value)
				: $value;

			foreach($values as $key => $value) {
				$incomingCollection->add($this->makeObject($accessor, $object, $field, $value));
			}
		}

		$isInverse   = $objectMetadata->isAssociationInverseSide($field);
		$mappedField = $objectMetadata->getAssociationMappedByTargetField($field);

		foreach($collection as $i => $relatedObject) {
			if (!$incomingCollection->contains($relatedObject)) {
				$collection->remove($i);

				if ($isInverse) {
					$accessor->set($relatedObject, $mappedField, NULL);
				}
			}
		}

		return $incomingCollection;
	}

	public function convertToGetterValue(Accessor $accessor, $object, $field, $value)
	{
		if ($value instanceof Collection) {
			return $value;
		}

		return new ArrayCollection();
	}
}

