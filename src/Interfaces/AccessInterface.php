<?php
namespace Tenet;

/**
 *
 */
interface AccessInterface
{
	/**
	 * List the fields allowed to be accessed
	 *
	 * @return array An array of the fields allowed to be accessed
	 */
	public function listAccessibleFields();


	/**
	 * Generate a callable capable of getting a field value
	 *
	 * @param string $field The name of the field to generate the getter for
	 * @return Callable A getter callable
	 */
	public function generateGetterCallable($field);


	/**
	 * Generate a callable capable of setting a field value
	 *
	 * @param string $field The name of the field to generate the setter for
	 * @return Callable A setter callable
	 */
	public function generateSetterCallable($field);
}
