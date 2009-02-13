<?php

class As_MysqlDatabaseResult extends As_DatabaseResult
{
	public function getRow()
	{
		return mysql_fetch_assoc($this->result);
	}
	
	public function getNumRows()
	{
		return mysql_num_rows($this->result);
	}
	
	public function getResult($row, $field = 0)
	{
		return mysql_result($this->result, $row, $field);
	}
	
	public function freeResult()
	{
		return mysql_free_result($this->result);
	}
}

?>