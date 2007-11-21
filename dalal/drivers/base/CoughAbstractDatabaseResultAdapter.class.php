<?php

/**
 * undocumented class
 *
 * @package dalal
 * @author Lewis Zhang
 **/
abstract class CoughAbstractDatabaseResultAdapter
{
	protected $result = null;
	
	/**
	 * creates a new CoughAbstractDatabaseResultAdapter (variant) object from a db abstraction layer specific result object
	 *
	 * @return void
	 * @author Lewis Zhang
	 **/
	protected function __construct($result)
	{
		$this->result = $result;
	}
	
	//abstract public static function retrieveByResult($result);
	
	abstract public function getRow();
	
	abstract public function getRows();
	
	abstract public function getNumRows();
	
	abstract public function result($rowNum);
	
	abstract public function freeResult();
	
} // END abstract class CoughAbstractDatabaseResultAdapter

?>
