<?php
namespace Tenet;

interface FilterInterface {
	public function convertToGetterValue(Accessor $accessor, $object, $field, $value);
	public function convertToSetterValue(Accessor $accessor, $object, $field, $value);
}
