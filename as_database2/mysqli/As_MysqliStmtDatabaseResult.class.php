<?php

class As_MysqlStmtDatabaseResult extends As_DatabaseResult
{
	private $stmt;
	private $params = false;
	private $row = false;

	public function __construct($stmt)
	{
		$this->stmt = $stmt;
		$meta = mysqli_stmt_result_metadata($stmt);
		$params = array($stmt);
		$this->row = array();
	    while ($field = $meta->fetch_field())
	    {
			$this->row[$field->name] = null;
	        $params[] = &$this->row[$field->name];
	    }
		call_user_func_array('mysqli_stmt_bind_result', $params);
		mysqli_stmt_store_result($stmt);
	}


	public function _freeResult()
	{
		if ($this->stmt)
		{
			$freed = $this->_freeResult();
			$this->stmt = null;
			return $freed;
		}
		return true;
	}
		
	public function getRow() 
	{
		if ($this->stmt->fetch())
		{
			$currRow = array();
	        foreach($this->row as $key => $val)
	        {
	            $currRow[$key] = $val;
	        }
			return $currRow;
		}
		return false;	
	}	
	
	public function getNumRows() 
	{
		return mysqli_stmt_num_rows($this->stmt);
	}
	
	public function getResult($row, $field = 0) 
	{
		mysqli_stmt_data_seek($this->result, $row);
		$this->stmt->fetch();
		if (!is_numeric($field))
		{
			return $this->row[$field];
		}
		$count = 0;
		foreach ($this->row as $key => $val)
		{
			if ($count++ == $field)
			{
				return $val;
			}
		}
		return null;
	}


	
}


?>