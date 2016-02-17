<?php

require_once(dirname(__FILE__).'/../_support/NostoProduct.php');
require_once(dirname(__FILE__).'/../_support/NostoOrder.php');

class ExportCollectionTest extends \Codeception\TestCase\Test
{
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	/**
	 * Tests that the export collection does not accept string items.
	 */
	public function testCollectionValidationForString()
	{
		$this->setExpectedException('NostoException');
		$collection = new NostoExportProductCollection();
		$collection[] = 'invalid item type';
	}

	/**
	 * Tests that the export collection does not accept integer items.
	 */
	public function testCollectionValidationForInteger()
	{
		$this->setExpectedException('NostoException');
		$collection = new NostoExportProductCollection();
		$collection->append(1);
	}

	/**
	 * Tests that the export collection does not accept float items.
	 */
	public function testCollectionValidationForFloat()
	{
		$this->setExpectedException('NostoException');
		$collection = new NostoExportProductCollection();
		$collection->append(99.99);
	}

	/**
	 * Tests that the export collection does not accept array items.
	 */
	public function testCollectionValidationForArray()
	{
		$this->setExpectedException('NostoException');
		$collection = new NostoExportProductCollection();
		$collection[] = array('test');
	}

	/**
	 * Tests that the export collection does not accept stdClass items.
	 */
	public function testCollectionValidationForObject()
	{
		$this->setExpectedException('NostoException');
		$collection = new NostoExportProductCollection();
		$collection->append(new stdClass());
	}
}
