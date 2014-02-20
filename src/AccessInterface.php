<?php
namespace Tenet;

interface AccessInterface {
	/**
	 * @param mixed $context The context identifier
	 * @return array An array of the fields to protect
	 */
	public function loadProtectedFields($context);
}
