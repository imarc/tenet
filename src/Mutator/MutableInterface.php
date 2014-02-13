<?php
namespace Tenet\Mutator;

interface MutableInterface {
	public function filter(Mutator $em, $field, $value);
	public function loadProtectedProperties();
	public function fill(Mutator $em, $data = array());
	public function set(Mutator $mutator, $field, $value);
}
