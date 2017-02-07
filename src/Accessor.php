<?php
namespace Tenet;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Doctrine Object Accessor
 *
 * Dynamic setting and getting of field values
 * on an object that is managed by Doctrine.
 *
 * @author Jeff Turcotte <jeff@imarc.net>
 *
 * @version 1.0.0
 *
 * @todo more association tests
 * @todo allow type registration for transient fields
 */
class Accessor {
	const ASSOCIATION_TO_ONE  = 'association_to_one';
	const ASSOCIATION_TO_MANY = 'association_to_many';

	const SETTER = 1;
	const GETTER = 2;

	protected $context;
	protected $manager;
	protected $filters;


	/**
	 * Constructor
	 *
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 *     An Object Manager
	 */
	public function __construct(ObjectManager $manager)
	{
		$this->context = null;
		$this->filters = [];
		$this->manager = $manager;

		// default orm/odm configuration

		$datetimeFilter = new Filter\DateTimeFilter();
		$booleanFilter  = new Filter\BooleanFilter();
		$fileFilter     = new Filter\FileFilter();

		$this->addTypeFilter('datetimetz', $datetimeFilter);
		$this->addTypeFilter('datetime',   $datetimeFilter);
		$this->addTypeFilter('date',       $datetimeFilter);
		$this->addTypeFilter('time',       $datetimeFilter);
		$this->addTypeFilter('bool',       $booleanFilter);
		$this->addTypeFilter('boolean',    $booleanFilter);
		$this->addTypeFilter('file',       $fileFilter);

		$this->addTypeFilter(self::ASSOCIATION_TO_MANY, new Filter\AssociationToManyFilter());
		$this->addTypeFilter(self::ASSOCIATION_TO_ONE, new Filter\AssociationToOneFilter());

	}


	/**
	 * Register an accessor filter allowing for value
	 * manipulationg pre-set
	 *
	 * @param string $type
	 *     The ObjectManager field type to associate the filter with
	 *
	 * @param FilterInterface filter
	 *     The filter implementation
	 *
	 * @return void
	 */
	public function addTypeFilter($type, FilterInterface $filter)
	{
		$this->filters[$type] = $filter;
	}


	/**
	 * Convert a value
	 *
	 * @param AccessInterface $object
	 *     The object to convert for
	 *
	 * @param string $field
	 *     The field being converted
	 *
	 * @param mixed $value
	 *     The value to convert
	 *
	 * @param integer $conversion
	 *     The type of conversion to do. One of Accessor::SETTER, Accessor::GETTER
	 *
	 * @return mixed
	 *     The converted value
	 */
	public function convert(AccessInterface $object, $field, $value, $conversion)
	{
		$type = $this->getFieldType($object, $field);

		if (isset($this->filters[$type])) {
			if ($conversion == self::SETTER) {
				$value = $this->filters[$type]->convertToSetterValue($this, $object, $field, $value);
			} else if ($conversion == self::GETTER) {
				$value = $this->filters[$type]->convertToGetterValue($this, $object, $field, $value);
			}
		}

		if ($type == 'integer' && $value == '') {
			return NULL;
		}

		return $value;
	}


	/**
	 * Set all fields of an object with the supplied data
	 *
	 * @param object $object
	 *     The managed object to fill
	 *
	 * @param array $data
	 *     The data array to fill the object with
	 *
	 * @param mixed $context
	 *     An optional context identifier or object passed
	 *     to any AccessInterface call to allow for dynamically
	 *     altering Accessor conditions
	 *
	 * @return object
	 *     The object passed as the first argumnent
	 */
	public function fill(AccessInterface $object, $data = array(), $files = array())
	{
		$data    = array_replace_recursive($files, $data);
		$allowed = $object->loadFillableFields($this);

		foreach($data as $name => $value) {
			if (in_array($name, $allowed)) {
				$this->set($object, $name, $value);
			}
		}

		return $object;
	}


	/**
	 * Set a single field on an object
	 *
	 * @param object $object
	 *     The object to set the field on
	 *
	 * @param string $field
	 *     The name of the field to set
	 *
	 * @param mixed $value
	 *     The value to filter and set on the object
	 *
	 * @return object
	 *     The object passed as the first argument
	 */
	public function get(AccessInterface $object, $field)
	{
		$getter = $object->generateGetterCallable($this, $field);

		if (is_callable($getter)) {
			$value = $this->convert($object, $field, $getter(), self::GETTER);
		} else {
			$value = $this->convert($object, $field, $object->get($field), self::GETTER);
		}

		return $value;
	}


	/**
	 * Gets the context for this accessor
	 *
	 * @return mixed
	 *     The context
	 */
	public function getContext()
	{
		return $context;
	}


	/**
	 * Gets the type of a field. Treats associations as types.
	 *
	 * @param object $object
	 *     The object
	 *
	 * @param string $field
	 *     The field to get the type of
	 *
	 * @return string
	 *     The ObjectManager's name of the type
	 */
	public function getFieldType($object, $field)
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


	/**
	 * Get the associated ObjectManager
	 *
	 * @return Doctrine\Common\Persistence\ObjectManager
	 */
	public function getObjectManager($class)
	{
		return $this->manager;
	}


	/**
	 * Set a single field on an object
	 *
	 * @param object $object
	 *     The object to set the field on
	 *
	 * @param string $field
	 *     The name of the field to set
	 *
	 * @param mixed $value
	 *     The value to filter and set on the object
	 *
	 * @return object
	 *     The object passed as the first argument
	 */
	public function set(AccessInterface $object, $field, $value, $internal = FALSE)
	{
		$callback  = $object->generateSetterCallable($this, $field);
		$new_value = $this->convert($object, $field, $value, self::SETTER);

		if (is_callable($callback)) {
			$callback($new_value);
		} else {
			$object->set($field, $new_value);
		}

		$class     = get_class($object);
		$manager   = $this->getObjectManager($class);
		$metadata  = $manager->getClassMetadata($class);
		$mappings  = $metadata->getAssociationMappings();
		$old_value = $this->get($object, $field);

		if (!isset($mappings[$field]) || $internal) {
			return $object;
		}

		// var_dump($mappings[$field]); exit();

		switch($mappings[$field]['type']) {
			case ClassMetadata::ONE_TO_ONE:
				if ($mappings[$field]['inversedBy']) {
					$this->set($new_value, $mappings[$field]['inversedBy'], $object, TRUE);
				} elseif ($mappings[$field]['mappedBy']) {
					$this->set($new_value, $mappings[$field]['mappedBy'], $object, TRUE);
				}
				break;

			case ClassMetadata::ONE_TO_MANY:
				if ($mappings[$field]['mappedBy']) {
					foreach ($old_value as $value) {
						if ($new_value->contains($value)) {
							continue;
						}

						$this->set($value, $mappings[$field]['mappedBy'], NULL, TRUE);
					}

					foreach ($new_value as $value) {
						if ($old_value->contains($value)) {
							continue;
						}

						$this->set($value, $mappings[$field]['mappedBy'], $object, TRUE);
					}
				}
				break;

			case ClassMetadata::MANY_TO_ONE:
				if ($mappings[$field]['inversedBy']) {
					$collection = $object->get($mappings[$field]['inversedBy']);

					if (!$collection->contains($object)) {
						$collection->add($object);
					}
				}

				break;

			case ClassMetadata::MANY_TO_MANY:

				break;
		}

		return $object;
	}


	/**
	 * Sets the context for this accessor
	 *
	 * @param mixed $context
	 *     The context
	 *
	 * @return void
	 */
	public function setContext($context)
	{
		$this->context = $context;
	}
}
