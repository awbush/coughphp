<?php

/**
 * Database Result class which the {@link As_Database::query()} returns.
 *
 * @package as_database
 **/
abstract class As_DatabaseResult
{
	abstract public function getRow();
	abstract public function getNumRows();
	abstract public function getResult($row, $field = 0);
	abstract public function freeResult();
	
	protected $result;

	public function __construct($result)
	{
		$this->result = $result;
	}
	
	// public function __destruct()
	// {
	// 	@$this->freeResult();
	// }

	public function getRows()
	{
		$rows = array();
		while ($row = $this->getRows())
		{
			$rows[] = $row;
		}
		return $rows;
	}
	
	/**
	 * Override below if implemented (e.g. MSSQL).
	 * 
	 * Example Usage:
	 * 
	 * <code>
	 * do
	 * {
	 *     while ($row = $result->getRow())
	 *     {
	 *         // process $row
	 *     }
	 * }
	 * while ($result->nextResult())
	 * </code>
	 *
	 * @return bool true if another result set is available, false if not
	 * @see http://www.php.net/manual/en/function.mssql-next-result.php
	 * @todo verify this method is clearly named, actually needed, and whether/not can be done for other engines
	 **/
	public function nextResult()
	{
		return false;
	}
	
}

?>