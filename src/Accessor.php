<?php
namespace Tenet;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * Doctrine Object Accessor
 *
 * @author Jeff Turcotte <jeff@imarc.net>
 *
 * @todo docs
 * @todo more association tests
 * @todo consider renaming Filter to Type
 * @todo allow type registration for transient fields
 */
class Accessor {
	const ASSOCIATION_TO_ONE  = 'association_to_one';
	const ASSOCIATION_TO_MANY = 'association_to_many';

	protected $manager;
	protected $filters = array();

	/**
	 *
	 */
	public function __construct(ObjectManager $manager)
	{
		$this->manager = $manager;

		// default orm/odm configuration

		$datetimeFilter = new Filter\DateTimeFilter();
		$fileFilter     = new Filter\FileFilter();

		$this->register('datetimetz', $datetimeFilter);
		$this->register('datetime',   $datetimeFilter);
		$this->register('date',       $datetimeFilter);
		$this->register('time',       $datetimeFilter);
		$this->register('file',       $fileFilter);

		$this->register(self::ASSOCIATION_TO_MANY, new Filter\AssociationToManyFilter());
		$this->register(self::ASSOCIATION_TO_ONE, new Filter\AssociationToOneFilter());

	}


	/**
	 *
	 */
	public function fill($object, $data = array())
	{
		$protected = [];

		if ($object instanceof AccessInterface) {
			$protected = $object->loadProtectedFields();
		}

		foreach($data as $name => $value) {
			if (!in_array($name, $protected)) {
				$this->set($object, $name, $value);
			}
		}

		return $object;
	}


	/**
	 *
	 */
	public function register($type, FilterInterface $filter)
	{
		$this->filters[$type] = $filter;
	}


	/**
	 *
	 */
	public function set($object, $field, $value)
	{
		$type = $this->getType($object, $field);

		if (isset($this->filters[$type])) {
			$value = $this->filters[$type]->convertToSetterValue($this, $object, $field, $value);
		}

		$setter = [$object, 'set' . ucfirst($field)];

		if (is_callable($setter)) {
			return $setter($value);
		}

		$this->getReflectionProperty($object, $field)->setValue($object, $value);

		return $object;
	}


	/**
	 *
	 */
	public function getObjectManager()
	{
		return $this->manager;
	}


	/**
	 *
	 */
	public function get($object, $field)
	{
		$getter = [$object, 'get' . ucfirst($field)];

		if (is_callable($getter)) {
			return $getter($value);
		}

		$value = $this->getReflectionProperty($object, $field)->getValue($object);
		$type  = $this->getType($object, $field);

		if (isset($this->filters[$type])) {
			return $this->filters[$type]->convertToGetterValue($this, $object, $field, $value);
		}

		return $value;
	}


	protected function getReflectionProperty($object, $property)
	{
		$property = new \ReflectionProperty($object, $property);
		$property->setAccessible(true);
		return $property;
	}

	/**
	 *
	 */
	protected function getType($object, $field)
	{
		$metadata  = $this->manager->getClassMetadata(get_class($object));

		if ($metadata->hasField($field)) {
			return $metadata->getTypeOfField($field);
		} else if ($metadata->hasAssociation($field) && $metadata->isSingleValuedAssociation($field)) {
			return self::ASSOCIATION_TO_ONE;
		} else if ($metadata->hasAssociation($field) && $metadata->isCollectionValuedAssociation($field)) {
			return self::ASSOCIATION_TO_MANY;
		}

		return null;
	}
}
