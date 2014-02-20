<?php
namespace Tenet;

interface AccessInterface {
	/**
	 * @return array An array of the fields to protect
	 */
	public function loadProtectedFields();
}
