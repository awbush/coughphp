<?php

class As_MysqliResultDatabaseResult extends As_DatabaseResult
{
	public function getRow()
	{
		return mysqli_fetch_assoc($this->result);
	}
	
	public function getNumRows()
	{
		return mysqli_num_rows($this->result);
	}
	
	public function getResult($row, $field = 0)
	{
		mysqli_data_seek($this->result, $row);
		$row = mysqli_fetch_row($this->result);
		return $row[$field];
	}
	
	public function _freeResult()
	{
		return mysqli_free_result($this->result);
	}
}

?>