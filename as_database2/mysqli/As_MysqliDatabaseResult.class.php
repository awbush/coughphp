<?php

/**
 * Database Result class which the {@link As_Database::query()} returns.
 *
 * @package as_database
 **/

require_once(dirname(__FILE__) . '/As_MysqliStmtDatabaseResult.class.php');
require_once(dirname(__FILE__) . '/As_MysqliResultDatabaseResult.class.php');

class As_MysqliDatabaseResult extends As_DatabaseResult {
	private $obj;
	private $freed = false;

	public function __construct($result) {
		if ($result instanceof MySQLi_STMT)
		{
			$this->obj = new As_MysqlStmtDatabaseResult($result);
		}
		else
		{	
			$this->obj = new As_MysqliResultDatabaseResult($result);
		}
	}

	public function getRow() {
		return $this->obj->getRow();
	}
	
	public function getRows()
	{
		return $this->obj->getRows();
	}
		
	public function getNumRows() {
		return $this->obj->getNumRows();
	}

	public function numRows() {
		return $this->getNumRows();
	}
	
	public function getResult($row, $field = 0) {
		return $this->obj->getResult($row, $field);
	}
	
	public function _freeResult() {
		return $this->obj-> _freeResult();
	}
	
}

?>