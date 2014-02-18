<?php
namespace Tenet\Filter;

use Tenet\FilterInterface;
use Tenet\Accessor;

class AssociationToOneFilter extends AbstractAssociationFilter implements FilterInterface
{
	public function convertToSetterValue(Accessor $accessor, $object, $field, $value)
	{
		$metadata = $accessor->getObjectManager()->getClassMetadata(get_class($object));
		$target   = $metadata->getAssociationTargetClass($object);

		return $this->makeObject($accessor->getObjectManager(), $object, $target, $value);
	}

	public function convertToGetterValue(Accessor $accessor, $object, $field, $value)
	{
		return $value;
	}
}

