<?php
namespace Tenet\Filter;

use Doctrine\Common\Collections\ArrayCollection;
use Tenet\FilterInterface;
use Tenet\Accessor;

class AssociationToManyFilter extends AbstractAssociationFilter implements FilterInterface
{
	public function convertToSetterValue(Accessor $accessor, $object, $field, $value)
	{
		$metadata   = $accessor->getObjectManager()->getClassMetadata(get_class($object));
		$target     = $metadata->getAssociationTargetClass($object);
		$collection = $accessor->get($object, $field) ?: new ArrayCollection();

		$values = !is_array($value) ? array($value) : $value;

		// clear the collection
		$collection->clear();
		foreach($values as $value) {
			$targetObject = $this->makeObject($accessor, $object, $target, $value);
			$collection->add($targetObject);
		}

		return $collection;
	}

	public function convertToGetterValue(Accessor $accessor, $object, $field, $value)
	{
		if ($value instanceof ArrayCollection) {
			return $value;
		}

		return new ArrayCollection();
	}
}

