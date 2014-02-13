<?php
namespace Entity\Mutator;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\EntityManager as Manager;

/**
 * Handles dynamic field setting for a Doctrine Entity.
 *
 * @author Jeff Turcotte <jeff@imarc.net>
 * @version 1.0.0
 */
class Mutator
{
	protected $em;

	/**
	 * Constructor
	 *
	 * @param Doctrine\ORM\EntityManager $em
	 *     The entity manager to use
	 *
	 * @return void
	 */
	public function __construct(Manager $manager)
	{
		$this->manager = $manager;
	}


	/**
	 * Calls Entity setters for each field => value pair in the
	 * supplied data array for fields that have been mapped.
	 *
	 * @param Entity\Mutator\MutableInterface $entity
	 *     The entity to fill
	 *
	 * @param array $data
	 *     The data to fill the entity with
	 *
	 * @return void
	 */
	public function fill(MutableInterface $entity, $data = array())
	{
		return $entity->fill($this, $data);
	}


	/**
	 * Filters a value for a specific entity
	 *
	 * @param Entity\Mutator\MutableInterface $entity
	 *     The entity to fill
	 *
	 * @param array $data
	 *     The data to fill the entity with
	 *
	 * @return void
	 */
	public function filter(MutableInterface $entity, $field, $value)
	{
		return $entity->fill($this, $field, $value);
	}

	/**
	 * Call an Entity setter for the specified field. This method
	 * supports transient fields, i.e. fields that are not mapped.
	 *
	 * @param Entity\Mutator\MutatableInterface $entity
	 *     The entity to set the field on
	 *
	 * @param string $field
	 *     The name of the field
	 *
	 * @param string $value
	 *     The value to set
	 *
	 * @return mixed
	 *     The return value of the Entity setter method
	 */
	public function set(MutableInterface $entity, $field, $value)
	{
		return $entity->set($this, $field, $value);
	}

	public function getManager()
	{
		return $this->manager;
	}


	/**
	 *
	 */
	public function filterFieldValue($entity, $field, $value)
	{
		$fields  = $this->manager->getClassMetadata(get_class($entity))->fieldMappings;
		$conn    = $this->manager->getConnection();
		
		return (isset($fields[$field]))
			? $connection->convertToPHPValue($value, $fields[$field]['type'])
			: $value;
	}

	/**
	 *
	 */
	public function filterToOneAssociationValue($entity, $field, $value)
	{
		$manager  = $this->manager->getManager();
		$mappings = $this->manager->getClassMetadata(get_class($entity))->associationMappings;
		$target   = $mappings[$field]['targetEntity'];

		return (! $value instanceof $target)
			? $em->getReference($target, $value)
			: $value;
	}

	/**
	 *
	 */
	public function filterToManyAssociationValue($entity, $field, $value)
	{
		if ($value instanceof Collection) {
			return $value;
		}

		$manager  = $this->manager;
		$mappings = $this->manager->getClassMetadata(get_class($this))->associationMappings;
		$target   = $mappings[$field]['targetEntity'];

		$value = new Collection((array) $value);
		return $value->map(function($v) use ($target, $manager) {
			return (! $value instanceof $target)
				? $manager->getReference($target, $v)
				: $value;
		});
	}
}

