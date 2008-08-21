<?php

class TestCoughCollection extends UnitTestCase
{
	//////////////////////////////////////
	// Set Up
	//////////////////////////////////////
	
	public function __construct()
	{
		$coughRoot = dirname(dirname(dirname(__FILE__)));
		require_once($coughRoot . '/cough/load.inc.php');
		require_once(dirname(__FILE__) . '/config/SortableElement.class.php');
		require_once(dirname(__FILE__) . '/config/SortableCollection.class.php');
	}
	
	//////////////////////////////////////
	// Test Methods
	//////////////////////////////////////
	
	public function buildSortableCollection()
	{
		// Data
		$sortableData = array(
			array(
				'element_id' => 1,
				'manufacturer_name' => 'Manuf A',
				'product_name' => 'Product A',
				'price' => 10.00,
			),
			array(
				'element_id' => 2,
				'manufacturer_name' => 'Manuf B',
				'product_name' => 'Product B',
				'price' => 11.00,
			),
			array(
				'element_id' => 3,
				'manufacturer_name' => 'Manuf C',
				'product_name' => 'Product C',
				'price' => 12.00,
			),
			array(
				'element_id' => 4,
				'manufacturer_name' => 'Manuf B',
				'product_name' => 'Product A',
				'price' => 22.00,
			),
		);
		
		// Create the collection
		$collection = new SortableCollection();
		$collection->add(new SortableElement($sortableData[0]));
		$collection->add(new SortableElement($sortableData[1]));
		$collection->add(new SortableElement($sortableData[2]));
		$collection->add(new SortableElement($sortableData[3]));
		
		return $collection;
	}
	
	public function testSortByMethod()
	{
		// Data
		$sortableData = array(
			array(
				'element_id' => 1,
				'manufacturer_name' => 'Manuf A',
				'product_name' => 'Product A',
				'price' => 10.00,
			),
			array(
				'element_id' => 2,
				'manufacturer_name' => 'Manuf B',
				'product_name' => 'Product B',
				'price' => 11.00,
			),
			array(
				'element_id' => 3,
				'manufacturer_name' => 'Manuf C',
				'product_name' => 'Product C',
				'price' => 12.00,
			),
		);
		
		$sortedCollectionIds = array(1,2,3);
		
		// Create a sorted copy and test that it matches before and after sorting
		$sortMe1 = new SortableCollection();
		$sortMe1->add(new SortableElement($sortableData[0]));
		$sortMe1->add(new SortableElement($sortableData[1]));
		$sortMe1->add(new SortableElement($sortableData[2]));
		$this->assertEqual($sortedCollectionIds, $sortMe1->getArrayKeys());
		$sortMe1->sortByMethod('getManufacturerName');
		$this->assertEqual($sortedCollectionIds, $sortMe1->getArrayKeys());
		unset($sortMe1);
		
		// Create another sorted copy and sort it using another method
		$sortMe2 = new SortableCollection();
		$sortMe2->add(new SortableElement($sortableData[0]));
		$sortMe2->add(new SortableElement($sortableData[1]));
		$sortMe2->add(new SortableElement($sortableData[2]));
		$sortMe2->sortByMethod('getProductName');
		$this->assertEqual($sortedCollectionIds, $sortMe2->getArrayKeys());
		unset($sortMe2);
		
		// Create another sorted copy and sort it using another method
		$sortMe3 = new SortableCollection();
		$sortMe3->add(new SortableElement($sortableData[0]));
		$sortMe3->add(new SortableElement($sortableData[1]));
		$sortMe3->add(new SortableElement($sortableData[2]));
		$sortMe3->sortByMethod('getPrice');
		$this->assertEqual($sortedCollectionIds, $sortMe3->getArrayKeys());
		unset($sortMe3);
		
		///////////////////////////////////////////
		// Now create unsorted copies and sort them
		///////////////////////////////////////////
		
		// Create an unsorted copy and sort it
		$sortMe4 = new SortableCollection();
		$sortMe4->add(new SortableElement($sortableData[1]));
		$sortMe4->add(new SortableElement($sortableData[2]));
		$sortMe4->add(new SortableElement($sortableData[0]));
		$this->assertNotEqual($sortedCollectionIds, $sortMe4->getArrayKeys());
		$sortMe4->sortByMethod('getManufacturerName');
		$this->assertEqual($sortedCollectionIds, $sortMe4->getArrayKeys());
		unset($sortMe4);
		
		// Create another unsorted copy and sort it using another method
		$sortMe5 = new SortableCollection();
		$sortMe5->add(new SortableElement($sortableData[1]));
		$sortMe5->add(new SortableElement($sortableData[2]));
		$sortMe5->add(new SortableElement($sortableData[0]));
		$this->assertNotEqual($sortedCollectionIds, $sortMe5->getArrayKeys());
		$sortMe5->sortByMethod('getProductName');
		$this->assertEqual($sortedCollectionIds, $sortMe5->getArrayKeys());
		unset($sortMe5);
		
		// Create another unsorted copy and sort it using another method
		$sortMe6 = new SortableCollection();
		$sortMe6->add(new SortableElement($sortableData[1]));
		$sortMe6->add(new SortableElement($sortableData[2]));
		$sortMe6->add(new SortableElement($sortableData[0]));
		$this->assertNotEqual($sortedCollectionIds, $sortMe6->getArrayKeys());
		$sortMe6->sortByMethod('getProductName');
		$this->assertEqual($sortedCollectionIds, $sortMe6->getArrayKeys());
		unset($sortMe6);
		
	}
	
	public function testSortByMethods()
	{
		$collection = $this->buildSortableCollection();
		
		$collection->sortByMethods('getManufacturerName');
		$this->assertEqual(array(1,4,2,3), $collection->getArrayKeys());
		
		$collection->sortByMethods('getManufacturerName', SORT_DESC);
		$this->assertEqual(array(3,2,4,1), $collection->getArrayKeys());
		
		$collection->sortByMethods('getManufacturerName', 'getProductName');
		$this->assertEqual(array(1,4,2,3), $collection->getArrayKeys());
		
		$collection->sortByMethods('getManufacturerName', 'getProductName', SORT_DESC);
		$this->assertEqual(array(1,2,4,3), $collection->getArrayKeys());
		
		$collection->sortByMethods('getManufacturerName', SORT_DESC, 'getProductName', SORT_DESC);
		$this->assertEqual(array(3,2,4,1), $collection->getArrayKeys());
		
		$collection->sortByMethods('getProductName', 'getManufacturerName');
		$this->assertEqual(array(1,4,2,3), $collection->getArrayKeys());
		
		$collection->sortByMethods('getProductName', 'getManufacturerName', SORT_DESC);
		$this->assertEqual(array(4,1,2,3), $collection->getArrayKeys());
		
		$collection->sortByMethods('getProductName', SORT_DESC, 'getManufacturerName', SORT_DESC);
		$this->assertEqual(array(3,2,4,1), $collection->getArrayKeys());
		
		$collection->sortByMethods('getPrice');
		$this->assertEqual(array(1,2,3,4), $collection->getArrayKeys());
		
		$collection->sortByMethods('getPrice', SORT_DESC);
		$this->assertEqual(array(4,3,2,1), $collection->getArrayKeys());
		
		$collection->sortByMethods('getPrice', SORT_ASC, 'getProductName', SORT_DESC, 'getManufacturerName');
		$this->assertEqual(array(1,2,3,4), $collection->getArrayKeys());
		
		
		// test sort empty collection
		$emptyCollection = new SortableCollection();
		$collection->sortByMethods('getManufacturerName');


		// test sort empty collection
		$threwException = false; 
		try 
		{
			$collection->sortByMethods();
		}
		catch (Exception $e)
		{
			$threwException = true;
		}
		
		$this->assertTrue($threwException);
		
	}
	
	public function testSortByKeys()
	{
		$collection = $this->buildSortableCollection();
		
		$keyPermutations = array(
			array(1,2,3,4),
			array(1,2,4,3),
			
			array(1,3,2,4),
			array(1,3,4,2),
			
			array(1,4,2,3),
			array(1,4,3,2),
			
			array(2,1,3,4),
			array(2,1,4,3),
			
			array(2,3,1,4),
			array(2,3,4,1),
			
			array(2,4,1,3),
			array(2,4,3,1),
			
			array(3,1,2,4),
			array(3,1,4,2),
			
			array(3,2,1,4),
			array(3,2,4,1),
			
			array(3,4,1,2),
			array(3,4,2,1),
			
			array(4,1,2,3),
			array(4,1,3,2),
			
			array(4,2,1,3),
			array(4,2,3,1),
			
			array(4,3,1,2),
			array(4,3,2,1),
		);
		
		foreach ($keyPermutations as $keyPermutation)
		{
			$collection->sortByKeys($keyPermutation);
			$this->assertEqual($keyPermutation, $collection->getArrayKeys());
		}
	}
	
	public function testGetKeyValueIteratorWithGetKeyId()
	{
		$collection = $this->buildSortableCollection();
		
		// Lack of a second parameter to `getKeyValueIterator()` should mean the
		// `getKeyId()` method is used on every object.

		$manualData = array();
		foreach ($collection as $keyId => $element)
		{
			$manualData[$keyId] = $element->getProductName();
		}
		
		$iterator = $collection->getKeyValueIterator('getProductName');
		$iteratorData = array();
		foreach ($iterator as $keyId => $productName)
		{
			$iteratorData[$keyId] = $productName;
		}
		
		$this->assertIdentical($manualData, $iteratorData);
		
		// count should work
		$this->assertIdentical(count($manualData), count($iterator));
		$this->assertIdentical(count($collection), count($iterator));
		
		// empty should work
		$this->assertIdentical(empty($manualData), empty($iterator));
		$this->assertIdentical(empty($collection), empty($iterator));
	}
	
	public function testGetKeyValueIteratorWithCustomKeyId()
	{
		$collection = $this->buildSortableCollection();
		
		// Specifying the second parameter should work
		
		$manualData = array();
		foreach ($collection as $element)
		{
			$manualData[$element->getPrice()] = $element->getProductName();
		}
		
		$iterator = $collection->getKeyValueIterator('getProductName', 'getPrice');
		$iteratorData = array();
		foreach ($iterator as $keyId => $productName)
		{
			$iteratorData[$keyId] = $productName;
		}
		
		$this->assertIdentical($manualData, $iteratorData);
		
		// count should work
		$this->assertIdentical(count($manualData), count($iterator));
		$this->assertIdentical(count($collection), count($iterator));
		
		// empty should work
		$this->assertIdentical(empty($manualData), empty($iterator));
		$this->assertIdentical(empty($collection), empty($iterator));
	}
	
}

?>
