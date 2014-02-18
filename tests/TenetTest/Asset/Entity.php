<?php
namespace TenetTest\Asset;

use Doctrine\Common\Collections\ArrayCollection;

class Entity {
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
