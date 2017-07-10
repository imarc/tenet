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


	/**
	 *
	 */
	protected $filters = array();


	/**
	 *
	 */
	protected $registry = NULL;


	/**
	 * Constructor
	 *
	 * @param Registry $registry An object/entity manager registry
	 * @return void
	 */
	public function __construct(Registry $registry)
	{
		$this->registry = $registry;
	}


	/**
	 * Register an accessor filter allowing for value manipulating on setting and getting
	 *
	 * @param string $type The ObjectManager field type to associate the filter with
	 * @param FilterInterface $filter The filter implementation
	 * @return void
	 */
	public function addTypeFilter($type, FilterInterface $filter)
	{
		$this->filters[$type] = $filter;
	}


	/**
	 * Set all fields of an object with the supplied data
	 *
	 * @param object $object The managed object to fill
	 * @param array $data The data array to fill the object with
	 * @param array $files The files array to fill the object with
	 * @return object The object passed as the first argumnent
	 */
	public function fill(AccessInterface $object, $data = array(), $files = array())
	{
		$data    = array_replace_recursive($files, $data);
		$allowed = $object->listAccessibleFields($this);

		foreach($data as $name => $value) {
			if (in_array($name, $allowed)) {
				$this->set($object, $name, $value);
			}
		}

		return $object;
	}


	/**
	 * Get a single field on an object
	 *
	 * @param object $object The object to set the field on
	 * @param string $field The name of the field to get
	 * @return mixed The filtered value currently set on the field
	 */
	public function get(AccessInterface $object, $field)
	{
		$getter = $object->generateGetterCallable($field);

		if (!is_callable($getter)) {
			//
			// Throw some kind of exception
			//
		}

		return $this->convert($object, $field, $getter(), self::GETTER);
	}


	/**
	 * Gets the type of a field. Treats associations as types.
	 *
	 * @param object $object The object
	 * @param string $field The field to get the type of
	 * @return string The ObjectManager's name of the type
	 */
	public function getFieldType($object, $field)
	{
		$metadata  = $this->manager->getClassMetadata(get_class($object));

		if ($metadata->hasField($field)) {
			return $metadata->getTypeOfField($field);

		} elseif ($metadata->hasAssociation($field) && $metadata->isSingleValuedAssociation($field)) {
			return self::ASSOCIATION_TO_ONE;

		} elseif ($metadata->hasAssociation($field) && $metadata->isCollectionValuedAssociation($field)) {
			return self::ASSOCIATION_TO_MANY;

		} else {
			return NULL;
		}
	}


	/**
	 * Get the default ObjectManager for a given class
	 *
	 * @return Doctrine\Common\Persistence\ObjectManager The default object manager which manages the given class
	 */
	public function getObjectManager($class)
	{
		return $this->registry->getObjectManager($class);
	}


	/**
	 * Set a single field on an object
	 *
	 * @param object $object The object to set the field on
	 * @param string $field The name of the field to set
	 * @param mixed $value The value to filter and set on the object
	 * @param boolean $update_related Whether or not related records should be updated
	 * @return object The object passed as the first argument
	 */
	public function set(AccessInterface $object, $field, $value, $update_related = TRUE)
	{
		$class     = get_class($object);
		$new_value = $this->convert($object, $field, $value, self::SETTER);
		$old_value = $this->get($object, $field);
		$manager   = $this->getObjectManager($class);
		$metadata  = $manager->getClassMetadata($class);
		$mappings  = $metadata->getAssociationMappings();
		$setter    = $object->generateSetterCallable($field);

		if (!is_callable($setter)) {
			//
			// Throw some kind of exception
			//
		}

		$setter($new_value);

		if ($update_related && isset($mappings[$field])) {
			switch($mappings[$field]['type']) {
				case ClassMetadata::ONE_TO_ONE:
					if ($mappings[$field]['inversedBy']) {
						$this->set($new_value, $mappings[$field]['inversedBy'], $object, FALSE);
					} elseif ($mappings[$field]['mappedBy']) {
						$this->set($new_value, $mappings[$field]['mappedBy'], $object, FALSE);
					}

					break;

				case ClassMetadata::ONE_TO_MANY:
					if ($mappings[$field]['mappedBy']) {
						foreach ($old_value as $value) {
							if ($new_value->contains($value)) {
								continue;
							}

							$this->set($value, $mappings[$field]['mappedBy'], NULL, FALSE);
						}

						foreach ($new_value as $value) {
							if ($old_value->contains($value)) {
								continue;
							}

							$this->set($value, $mappings[$field]['mappedBy'], $object, FALSE);
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
		}

		return $object;
	}


	/**
	 * Convert a value
	 *
	 * @param AccessInterface $object The object to convert for
	 * @param string $field The field being converted
	 * @param mixed $value The value to convert
	 * @param integer $conversion The type of conversion to do. One of Accessor::SETTER, Accessor::GETTER
	 * @return mixed The converted value
	 */
	protected function convert(AccessInterface $object, $field, $value, $conversion)
	{
		$type = $this->getFieldType($object, $field);

		if (isset($this->filters[$type])) {
			if ($conversion == self::SETTER) {
				$value = $this->filters[$type]->convertToSetterValue($this, $object, $field, $value);

			} elseif ($conversion == self::GETTER) {
				$value = $this->filters[$type]->convertToGetterValue($this, $object, $field, $value);

			} else {
				throw new InvalidArgumentException(sprintf(
					'Invalid conversion type %s specified',
					$conversion
				));
			}
		}

		return $value;
	}
}
