<?php
namespace Tenet\Mutator;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Proxy\Proxy;
use Doctrine\ORM\Mapping\ClassMetadata;

trait MutableTrait
{
	public function loadProtectedProperties()
	{
		return [];
	}

	public function filter(Mutator $mutator, $field, $value)
	{
		$filter   = $this->generateFilter($field);
		$metadata = $mutator->getManager()->getClassMetadata(get_class($this));
		$fieldMap = $metadata->fieldMappings;
		$assocMap = $metadata->associationMappings;

		if (is_callable($filter)) {
			return $filter($value);
		} else if (isset($fieldMap[$field])) {
			return $mutator->filterFieldValue($this, $field, $value);
		} else if (isset($assocMap[$field]) && $assocMap[$field]['type'] & ClassMetadata::TO_ONE) {
			return $mutator->filterToOneAssociationValue($this, $field, $value);
		} else if (isset($assocMap[$field])) {
			return $mutator->filterToManyAssociationValue($this, $field, $value);
		}

		return $value;
	}


	public function fill(Mutator $mutator, $data = array())
	{
		$fields    = get_object_vars($this);
		$protected = $this->loadProtectedProperties();
		$manager   = $mutator->getManager();

		foreach($fields as $field => $value) {
			if (isset($data[$field]) && !in_array($field, $protected)) {
				$mutator->set($this, $field, $data[$field]);
			}
		}
	}


	public function generateSetter($field)
	{
		return [$this, 'set' . Inflector::classify($field)];
	}


	public function generateFilter($field)
	{
		return [$this, 'filter' . Inflector::classify($field)];
	}


	public function set(Mutator $mutator, $field, $value)
	{
		$manager = $mutator->getManager();

		$class  = get_class($this);
		$fields = get_object_vars($this);
		$setter = $this->generateSetter($field);

		$value = $mutator->filter($entity, $field, $value);

		if (in_array($fields[$field], $this->loadProtectedProperties())) {
			throw new \LogicException("{$class} property {$field} is protected");
		} else if (is_callable($setter)) {
			return $setter($value);
		} else if (!array_key_exists($field, $fields)) {
			throw new \LogicException("{$class} has no property {$field}");
		}

		$this->$field = $value;

		return $this;
	}
}
