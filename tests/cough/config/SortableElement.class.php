<?php

/**
 * A test cough object with the basic stuff: PK, fields, and accessors for them.
 *
 * @package tests
 * @author Anthony Bush
 **/
class SortableElement extends CoughObject
{
	protected static $pkFieldNames = array('element_id');
	protected $fieldDefinitions = array(
		'element_id' => array(),
		'manufacturer_name' => array(),
		'product_name' => array(),
		'price' => array(),
	);
	
	// CoughObjectStaticInterface
	
	public static function getPkFieldNames()
	{
		return self::$pkFieldNames;
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