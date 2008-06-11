<?php

/**
 * Custom iterator constructed by CoughCollection::getKeyValueIterator()
 * 
 * @package cough
 **/
class CoughKeyValueIterator extends CoughIterator
{
	protected $valueMethod = '';
	protected $keyMethod = '';
	
	public function __construct($arr, $valueMethod, $keyMethod = 'getKeyId')
	{
		parent::__construct($arr);
		$this->valueMethod = $valueMethod;
		$this->keyMethod = $keyMethod;
	}
	
	public function current()
	{
		return parent::current()->{$this->valueMethod}();
	}
	
	public function key()
	{
		return parent::current()->{$this->keyMethod}();
	}
}

?>