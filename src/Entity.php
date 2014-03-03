<?php
namespace Tenet;

/**
 *
 */
abstract class Entity implements AccessInterface
{
	public function loadProtectedFields($context)
	{
		return [];
	}
}