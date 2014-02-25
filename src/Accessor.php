<?php
namespace Tenet;

use Doctrine\Common\Persistence\ObjectManager;

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
		$fileFilter     = new Filter\FileFilter();

		$this->addTypeFilter('datetimetz', $datetimeFilter);
		$this->addTypeFilter('datetime',   $datetimeFilter);
		$this->addTypeFilter('date',       $datetimeFilter);
		$this->addTypeFilter('time',       $datetimeFilter);
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
	public function fill(AccessInterface $object, $data = array())
	{
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
			return $this->convert($object, $field, $getter(), self::GETTER);
		}

		return $object->get($this, $field);
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
	public function getObjectManager()
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
	public function set(AccessInterface $object, $field, $value)
	{
		$setter = $object->generateSetterCallable($this, $field);

		if (is_callable($setter)) {
			return $setter($this->convert($object, $field, $value, self::SETTER));
		}

		return $object->set($this, $field, $value);
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
