<?php

/**
 * Database Result class which the {@link As_Database::query()} returns.
 *
 * @package as_database
 **/
class As_DatabaseResult {
	private $result;
	private $freed = false;

	public function __construct($result) {
		$this->result = $result;
	}
	
	public function __destruct()
	{
		@$this->freeResult();
	}

	public function getRow() {
		return mysql_fetch_assoc($this->result);
	}
	
	public function getRows() {
		$rows = array();
		while ($row = mysql_fetch_assoc($this->result))
		{
			$rows[] = $row;
		}
		return $rows;
	}
	
	public function getNumRows() {
		return mysql_num_rows($this->result);
	}

	public function numRows() {
		return $this->getNumRows();
	}
	
	public function result($row) {
		return mysql_result($this->result, $row);
	}
	
	public function freeResult() {
		return mysql_free_result($this->result);
	}
}

?>