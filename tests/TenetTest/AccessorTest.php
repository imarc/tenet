<?php
/*
 * This file is part of the Tenet package.
 *
 * (c) Jeff Turcotte <jeff.turcotte@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace TenetTest;

use Tenet\Accessor;

class AccessorTest extends \PHPUnit_Framework_TestCase
{
	public function setUp() {
		$metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
		$manager  = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

		$entityClass = 'TenetTest\Asset\Entity';
		$refl = new \ReflectionClass($entityClass);

		$manager->expects($this->any())
			->method('getClassMetadata')
			->will($this->returnValue($metadata));

		$metadata
			->expects($this->any())
			->method('getName')
			->will($this->returnValue($entityClass));

		$metadata
			->expects($this->any())
			->method('getAssociationNames')
			->will($this->returnValue(array('toOne', 'toMany')));

		$metadata
			->expects($this->any())
			->method('getFieldNames')
			->will($this->returnValue(array('id', 'string', 'datetime')));

		$metadata
			->expects($this->any())
			->method('hasField')
			->with(
				$this->logicalOr(
					$this->equalTo('id'),
					$this->equalTo('string'),
					$this->equalTo('datetime'),
					$this->equalTo('toOne'),
					$this->equalTo('toMany')
				)
			)->will(
				$this->returnCallback(
					function ($arg) {
						if ('id' == $arg) {
							return true;
						} elseif ('string' == $arg) {
							return true;
						} elseif ('datetime' == $arg) {
							return true;
						}

						return false;
					}
				)
			);

		$metadata
			->expects($this->any())
			->method('getTypeOfField')
			->with(
				$this->logicalOr(
					$this->equalTo('id'),
					$this->equalTo('string'),
					$this->equalTo('datetime'),
					$this->equalTo('toOne'),
					$this->equalTo('toMany')
				)
			)->will(
				$this->returnCallback(
					function ($arg) use ($entityClass) {
						if ('id' === $arg) {
							return 'integer';
						} elseif ('string' === $arg) {
							return 'string';
						} elseif ('datetime' == $arg) {
							return 'datetime';
						} elseif ('toOne' == $arg) {
							return $entityClass;
						} elseif ('toMany' == $arg) {
							return 'Doctrine\Common\Collections\ArrayCollection';
						}
					}
				)
			);

		$metadata
			->expects($this->any())
			->method('hasAssociation')
			->with(
				$this->logicalOr(
					$this->equalTo('id'),
					$this->equalTo('string'),
					$this->equalTo('datetime'),
					$this->equalTo('toOne'),
					$this->equalTo('toMany')
				)
			)->will(
				$this->returnCallback(
					function ($arg) {
						if ('toOne' == $arg) {
							return true;
						} elseif ('toMany' == $arg) {
							return true;
						}

						return false;
					}
				)
			);

		$metadata
			->expects($this->any())
			->method('isSingleValuedAssociation')
			->with(
				$this->logicalOr(
					$this->equalTo('toOne'),
					$this->equalTo('toMany')
				)
			)->will(
				$this->returnCallback(
					function ($arg) {
						if ('toOne' == $arg) {
							return true;
						} elseif ('toMany' == $arg) {
							return false;
						}

						return false;
					}
				)
			);

		$metadata
			->expects($this->any())
			->method('isCollectionValuedAssociation')
			->with(
				$this->logicalOr(
					$this->equalTo('toOne'),
					$this->equalTo('toMany')
				)
			)->will(
				$this->returnCallback(
					function ($arg) {
						if ('toOne' == $arg) {
							return false;
						} elseif ('toMany' == $arg) {
							return true;
						}

						return false;
					}
				)
			);

		$metadata
			->expects($this->any())
			->method('getAssociationTargetClass')
			->will($this->returnValue($entityClass));

		$metadata
			->expects($this->any())
			->method('hasAssociation')
			->will($this->returnValue(true));

		$metadata
			->expects($this->any())
			->method('getIdentifierFieldNames')
			->will($this->returnValue(array('id')));

		$metadata
			->expects($this->any())
			->method('getReflectionClass')
			->will($this->returnValue($refl));

		$this->accessor = new Accessor($manager);
	}

	public function testFill()
	{
		$entity = new Asset\Entity;

		$this->accessor->fill($entity, array(
			'id' => 5,
			'string' => 'My String'
		));

		$id = $this->accessor->get($entity, 'id');
		$string = $this->accessor->get($entity, 'string');


		$this->assertEquals(5, $id);
		$this->assertEquals('My String', $string);
	}

	public function testDateTimeFilter()
	{
		$entity = new Asset\Entity;

		$this->accessor->fill($entity, array(
			'datetime' => '2014'
		));

		$date = $this->accessor->get($entity, 'datetime');


		$this->assertInstanceOf('DateTime', $date);
	}

	public function testToOneAssociationWithObject()
	{
		$entity = new Asset\Entity;

		$this->accessor->fill($entity, array(
			'toOne' => $entity
		));

		$this->assertEquals($entity, $this->accessor->get($entity, 'toOne'));
	}
}
