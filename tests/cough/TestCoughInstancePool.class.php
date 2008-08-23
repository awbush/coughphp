<?php

class TestCoughInstancePool extends UnitTestCase
{
	public function __construct()
	{
		$coughRoot = dirname(dirname(dirname(__FILE__)));
		require_once($coughRoot . '/cough/load.inc.php');
		require_once($coughRoot . '/cough/CoughInstancePool.class.php');
		require_once(dirname(__FILE__) . '/config/InstancePoolTestObject.class.php');
		require_once(dirname(__FILE__) . '/config/InstancePoolTestObject2.class.php');
	}
	
	public function testEverything()
	{
		// there should not be anything for a class that doesn't exist
		$this->assertFalse(CoughInstancePool::has('RandomClassFlipperGypperJam', 123));
		$this->assertNull(CoughInstancePool::get('RandomClassFlipperGypperJam', 123));
		
		// there should not be anything for a class that does exist when nothing has been added.
		$this->assertFalse(CoughInstancePool::has('InstancePoolTestObject', 1));
		$this->assertNull(CoughInstancePool::get('InstancePoolTestObject', 1));
		
		// we should be able to get a mock object (not a CoughInstancePool test)
		$obj = InstancePoolTestObject::constructFromHardCodedData(1);
		$this->assertTrue($obj instanceof InstancePoolTestObject);
		
		// there should be something in the pool if we add it first
		CoughInstancePool::add($obj, $obj->getKeyId());
		$this->assertTrue(CoughInstancePool::has('InstancePoolTestObject', $obj->getKeyId()));
		$this->assertTrue(CoughInstancePool::get('InstancePoolTestObject', $obj->getKeyId()) instanceof InstancePoolTestObject);
		
		// the item should be a reference to the same thing we gave it
		$this->assertIdentical($obj, CoughInstancePool::get('InstancePoolTestObject', $obj->getKeyId()));
		
		// the item should be removeable by passing the object and key
		CoughInstancePool::remove($obj, $obj->getKeyId());
		$this->assertFalse(CoughInstancePool::has('InstancePoolTestObject', $obj->getKeyId()));
		$this->assertNull(CoughInstancePool::get('InstancePoolTestObject', $obj->getKeyId()));

		// we should be able to re-add the same object.
		CoughInstancePool::add($obj, $obj->getKeyId());
		$this->assertTrue(CoughInstancePool::has('InstancePoolTestObject', $obj->getKeyId()));
		$this->assertTrue(CoughInstancePool::get('InstancePoolTestObject', $obj->getKeyId()) instanceof InstancePoolTestObject);
		
		// the item should be removeable by passing the class name and key
		CoughInstancePool::remove('InstancePoolTestObject', $obj->getKeyId());
		$this->assertFalse(CoughInstancePool::has('InstancePoolTestObject', $obj->getKeyId()));
		$this->assertNull(CoughInstancePool::get('InstancePoolTestObject', $obj->getKeyId()));
	}
	
	public function testMultipleObjectTypes()
	{
		// we should be able to add multiple objects of multiple classes
		$objA1 = InstancePoolTestObject::constructFromHardCodedData(1);
		$objA2 = InstancePoolTestObject::constructFromHardCodedData(2);
		$objB1 = InstancePoolTestObject2::constructFromHardCodedData(1);
		$objB2 = InstancePoolTestObject2::constructFromHardCodedData(2);

		// verify the objects we have are in fact different
		$uniqueEntities = array();
		$uniqueEntities[get_class($objA1) . '-' . $objA1->getKeyId()] = true;
		$uniqueEntities[get_class($objA2) . '-' . $objA2->getKeyId()] = true;
		$uniqueEntities[get_class($objB1) . '-' . $objB1->getKeyId()] = true;
		$uniqueEntities[get_class($objB2) . '-' . $objB2->getKeyId()] = true;
		$this->assertTrue(count($uniqueEntities) == 4, 'The instance pool test objects are messed up');
		
		// add them all
		CoughInstancePool::add($objA1, $objA1->getKeyId());
		CoughInstancePool::add($objA2, $objA2->getKeyId());
		CoughInstancePool::add($objB1, $objB1->getKeyId());
		CoughInstancePool::add($objB2, $objB2->getKeyId());

		// check they all come back good
		$this->assertIdentical($objA1, CoughInstancePool::get(get_class($objA1), $objA1->getKeyId()));
		$this->assertIdentical($objA2, CoughInstancePool::get(get_class($objA2), $objA2->getKeyId()));
		$this->assertIdentical($objB1, CoughInstancePool::get(get_class($objB1), $objB1->getKeyId()));
		$this->assertIdentical($objB2, CoughInstancePool::get(get_class($objB2), $objB2->getKeyId()));

		// remove two at random and check that they are gone and the others are still good
		CoughInstancePool::remove(get_class($objB1), $objB1->getKeyId());
		CoughInstancePool::remove(get_class($objA2), $objA2->getKeyId());
		$this->assertNull(CoughInstancePool::get(get_class($objB1), $objB1->getKeyId()));
		$this->assertNull(CoughInstancePool::get(get_class($objA2), $objA2->getKeyId()));
		$this->assertIdentical($objA1, CoughInstancePool::get(get_class($objA1), $objA1->getKeyId()));
		$this->assertIdentical($objB2, CoughInstancePool::get(get_class($objB2), $objB2->getKeyId()));
	}
}

?>