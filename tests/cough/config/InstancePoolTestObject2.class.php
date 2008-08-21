<?php

class InstancePoolTestObject2 extends InstancePoolTestObject
{
	public static function constructFromHardCodedData($id)
	{
		static $dbRecords = array(
			'1' => array(
				'element_id' => 1,
				'manufacturer_name' => 'Manuf Name 1',
				'product_name' => 'Product Name 1',
				'price' => 12.34,
			),
			'2' => array(
				'element_id' => 2,
				'manufacturer_name' => 'Manuf Name 2',
				'product_name' => 'Product Name 2',
				'price' => 23.45,
			),
		);
		
		if (isset($dbRecords[$id])) {
			return new InstancePoolTestObject2($dbRecords[$id]);
		} else {
			return null;
		}
	}
}

?>