<?php

class As_MssqlDatabaseResult extends As_DatabaseResult
{
	public function getRow()
	{
		return mssql_fetch_assoc($this->result);
	}
	
	public function getNumRows()
	{
		return mssql_num_rows($this->result);
	}
	
	public function getResult($row, $field = 0)
	{
		return mssql_result($this->result, $row, $field);
	}
	
	public function freeResult()
	{
		return mssql_free_result($this->result);
	}
	
	public function nextResult()
	{
		return mssql_next_result($this->result);
	}
}

?>