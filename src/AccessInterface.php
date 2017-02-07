<?php
namespace Tenet;

interface AccessInterface
{
	/**
	 * @param Tenet\Accessor $accessor
	 *    An accessor instance
	 *
	 * @return array
	 *    An array of the fields allowed to be set
	 */
	public function loadFillableFields(Accessor $accessor);


	/**
	 * @param Tenet\Accessor $accessor
	 *    An accessor instance
	 *
	 * @param string $field
	 *    The name of the field to generate the setter for
	 *
	 * @return Callable
	 *    A setter callable
	 */
	public function generateSetterCallable(Accessor $accessor, $field);


	/**
	 * @param Tenet\Accessor $accessor
	 *    An accessor instance
	 *
	 * @param string $field
	 *    The name of the field to generate the getter for
	 *
	 * @return Callable
	 *    A getter callable
	 */
	public function generateGetterCallable(Accessor $accessor, $field);


	/**
	 * @param Tenet\Accessor $accessor
	 *    An accessor instance
	 *
	 * @param string $field
	 *    The name of the field to get
	 *
	 * @return mixed
	 *    The value associated with the field
	 */
	public function get($field);


	/**
	 * @param Tenet\Accessor $accessor
	 *    An accessor instance
	 *
	 * @param string $field
	 *    The name of the field
	 *
	 * @param mixed $value
	 *    The value to set
	 *
	 * @return object
	 *    The object the field was set on
	 */
	public function set($field, $value);
}
