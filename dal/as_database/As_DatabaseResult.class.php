<?php

class As_DatabaseResult {
	private $result;

	public function __construct($result) {
		$this->result = $result;
	}

	public function getRow() {
		return mysql_fetch_assoc($this->result);
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