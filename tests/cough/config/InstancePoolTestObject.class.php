<?php

class InstancePoolTestObject extends CoughObject
{
	protected static $pkFieldNames = array('element_id');
	
	protected $fieldDefinitions = array(
		'element_id' => array(),
		'manufacturer_name' => array(),
		'product_name' => array(),
		'price' => array(),
	);
	
	public static function getPkFieldNames()
	{
		return self::$pkFieldNames;
	}
	
	// Just FYI, the generator would probably write methods like this
	// public static function constructByKey($id)
	// {
	// 	if (CoughInstancePool::has('InstancePoolTestObject', $id))
	// 	{
	// 		return CoughInstancePool::get('InstancePoolTestObject', $id);
	// 	}
	// 	else
	// 	{
	// 		$newMockObject = CoughObject::constructByKey($id, 'InstancePoolTestObject');
	// 		CoughInstancePool::add($newMockObject, $id);
	// 		return $newMockObject;
	// 	}
	// }
	// 
	// public static function constructBySql($sql)
	// {
	// 	return CoughObject::constructBySql($fields, 'InstancePoolTestObject');
	// }
	// 
	// public static function constructByFields($fields)
	// {
	// 	return new InstancePoolTestObject($fields);
	// }
	
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
			return new InstancePoolTestObject($dbRecords[$id]);
		} else {
			return null;
		}
	}
	
	// Accessors
	
	public function getElementId()
	{
		return $this->getField('element_id');
	}
	
	public function getManufacturerName()
	{
		return $this->getField('manufacturer_name');
	}
	
	public function getProductName()
	{
		return $this->getField('product_name');
	}
	
	public function getPrice()
	{
		return $this->getField('price');
	}
	
	public function setElementId($value)
	{
		return $this->setField('element_id', $value);
	}
	
	public function setManufacturerName($value)
	{
		return $this->setField('manufacturer_name', $value);
	}
	
	public function setProductName($value)
	{
		return $this->setField('product_name', $value);
	}
	
	public function setPrice($value)
	{
		return $this->setField('price', $value);
	}
}

?>