<?php
namespace Tenet\Filter;

use Tenet\FilterInterface;
use Tenet\Accessor;

class AssociationToOneFilter extends AbstractAssociationFilter implements FilterInterface
{
	public function convertToSetterValue(Accessor $accessor, $object, $field, $value)
	{
		$manager         = $accessor->getObjectManager();
		$objectMetadata  = $manager->getClassMetadata(get_class($object));
		$mappedField     = $objectMetadata->getAssociationMappedByTargetField($field);
		$mappedClass     = $objectMetadata->getAssociationTargetClass($field);
		$relatedObject   = $this->makeObject($accessor, $object, $field, $value);

		if ($relatedObject && $mappedField && ($mappedField != $field || $mappedClass != get_class($object))) {
			$accessor->set($relatedObject, $mappedField, $object);
		}

		return $relatedObject;
	}

	public function convertToGetterValue(Accessor $accessor, $object, $field, $value)
	{
		return $value;
	}
}
