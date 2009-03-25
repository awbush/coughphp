<?php

class As_MssqlDatabase extends As_Database
{
	protected $dsn = array(
		'host' => 'localhost',
		'user' => 'nobody',
		'pass' => '',
		'port' => 1433,
	);
	
	/**
	 * Construct new Mssql database object and connect to specified DSN, format of:
	 * 
	 * <code>
	 * array(
	 *     'host' => 'localhost',
	 *     'user' => 'nobody',
	 *     'pass' => '',
	 *     'port' => 1433,
	 *     'db_name' => 'default_db_name',
	 * )
	 * </code>
	 * 
	 * Everything is optional.
	 * 
	 * @return As_MssqlDatabase
	 **/
	public static function constructByConfig($dbConfig)
	{
		include_once('As_MssqlSelectQuery.class.php');
		return new As_MssqlDatabase($dbConfig);
	}
	
	protected function connect()
	{
		$hostAndPort = $this->dsn['host'] . ':' . $this->dsn['port'];		
		// @todo consider using @ (do some testing) and pass error messages to exception
		// instead. Also might try playing with `print_r(error_get_last());`
		$this->connection = mssql_connect($hostAndPort, $this->dsn['user'], $this->dsn['pass'], true);
		if (!$this->connection) {
			throw new As_DatabaseConnectException("mssql", $this->dsn["host"], $this->dsn["port"], $this->dsn["user"], mssql_get_last_message());
		}
		
		// select default DB if provided
		if (isset($this->dsn['db_name'])) {
			$this->selectDb($this->dsn['db_name']);
		} else {
			$this->dbName = null;
		}
	}
	
	public function disconnect()
	{
		if ($this->connection !== false)
		{
			mssql_close($this->connection);
		}
	}
	
	protected function _selectDb($dbName)
	{
		return mssql_select_db($dbName, $this->connection);
	}
	
	public function quote($value)
	{
		// Handle special PHP values and SQL Functions
		if ($value === null) {
			return 'NULL';
		} else if ($value === false) {
			return '0';
		} else if ($value === true) {
			return '1';
		} else if ($value instanceof As_SqlFunction) {
			return $value->__toString();
		}
		
		// @todo verify this for correctness. it doesn't do gpc check like MySQL version and instead strips slashes always...
		return "'" . stripslashes(str_replace("'", "''", $value)) . "'";
	}
	
	public function backtick($value)
	{
		return '[' . str_replace(']', '', $value) . ']';
	}
	
	protected function _query($sql)
	{
		$result = mssql_query($sql, $this->connection);
		if (!$result) {
			return $result;
		}
		return new As_MssqlDatabaseResult($result);
	}
	
	public function getNumAffectedRows()
	{
		return mssql_rows_affected($this->connection);
	}
	
	public function getLastInsertId()
	{
		// SCOPE_IDENTITY docs: http://msdn.microsoft.com/en-us/library/ms190315.aspx
		// IDENT_CURRENT docs: http://msdn.microsoft.com/en-us/library/ms175098.aspx
		return $this->result('SELECT SCOPE_IDENTITY()');
	}
	
	// @todo implement getNumFoundRows() for MSSQL
	public function getNumFoundRows()
	{
		throw new As_DatabaseException('getNumFoundRows not implemented');
	}
	
	public function getError()
	{
		if ($this->connection) {
			if ($this->inTransaction) {
				return 'Transaction Failed with MSSQL error: ' . mssql_get_last_message();
			} else {
				return mssql_get_last_message();
			}
		}
	}
	
	public function startTransaction()
	{
		$this->query('BEGIN TRANSACTION');
		$this->inTransaction = true;
	}
	
	public function commit()
	{
		$this->query('COMMIT');
		$this->inTransaction = false;
	}
	
	public function rollback()
	{
		$this->query('ROLLBACK');
		$this->inTransaction = false;
	}
	
	public function getSelectQuery()
	{
		return new As_MssqlSelectQuery($this);
	}
}

?>