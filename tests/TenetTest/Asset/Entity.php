<?php
namespace TenetTest\Asset;

use Doctrine\Common\Collections\ArrayCollection;
use Tenet\AccessInterface;
use Tenet\Access\AccessibleTrait;

class Entity implements AccessInterface {
	use AccessibleTrait;

	protected $id;
	protected $string;
	protected $datetime;
	protected $toOne;
	protected $toMany;

	public function __construct()
	{
		$this->toMany = new ArrayCollection();
	}
}
