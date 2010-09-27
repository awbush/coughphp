<?php
class As_DatabaseQueryException extends As_DatabaseException 
{
	private $sqlError;
	private $sql;

	public function __construct($sqlError, $sql) 
	{
		parent::__construct('Query error: ' . $sqlError . ' -- QUERY: ' . $sql);
		$this->sqlError = $sqlError;
		$this->sql = $sql;
	}

	public function getSqlError() 
	{
		return $this->sqlError;
	}

	public function getSql() 
	{
		return $this->sql;
	}
}
?>