<?php

/**
 * undocumented class
 *
 * @package default
 * @author Lewis Zhang
 **/
abstract class Lz_DatabaseResult
{
	protected $result = null;
	
	/**
	 * creates a new Lz_DatabaseResult object from an array of rows
	 *
	 * @return void
	 * @author Lewis Zhang
	 **/
	protected function __construct($result)
	{
		$this->result = $result;
	}
	
	abstract public static function retrieveByResult($result);
	
	abstract public function getRow();
	
	abstract public function getRows();
	
	abstract public function getNumRows();
	
	abstract public function result($rowNum);
} // END abstract class Lz_DatabaseResult

?>