<?php
class As_DatabaseQueryException extends As_DatabaseException 
{
	private $sqlError;
	private $sql;
	private $parameters;
	private $types;

	public function __construct($sqlError, $sql, $parameters = null, $types = null) 
	{
		$errorString = 'Query error: ' . $sqlError . ' -- QUERY: ' . $sql;
		if (!is_null($parameters))
		{
			$errorString .= ' -- PARAMETERS ' . print_r($parameters, true);
		}
		if (!is_null($types))
		{
			$errorString .= ' -- TYPES ' . $types;
		}
		
		parent::__construct($errorString);
		$this->sqlError = $sqlError;
		$this->sql = $sql;
		$this->parameters = $parameters;
		$this->types = $types;
	}

	public function getSqlError() 
	{
		return $this->sqlError;
	}

	public function getSql() 
	{
		return $this->sql;
	}
	
	public function getParameters()
	{
		return $this->parameters;
	}
	
	public function getTypes()
	{
		return $this->types;
	}
	
}
?>