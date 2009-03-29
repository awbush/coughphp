<?php

class As_MysqlDatabase extends As_Database
{
	protected $dsn = array(
		'host' => 'localhost',
		'user' => 'nobody',
		'pass' => '',
		'port' => 3306,
		'client_flags' => 0,
	);
	
	/**
	 * Construct new Mysql database object and connect to specified DSN, format of:
	 * 
	 * <code>
	 * array(
	 *     'host' => 'localhost',
	 *     'user' => 'nobody',
	 *     'pass' => '',
	 *     'port' => 3306,
	 *     'client_flags' => 0,
	 *     'db_name' => 'default_db_name',
	 * )
	 * </code>
	 * 
	 * Everything is optional.
	 * 
	 * @return void
	 * @author Anthony Bush
	 **/
	public static function constructByConfig($dbConfig)
	{
		return new As_MysqlDatabase($dbConfig);
	}
	
	protected function connect()
	{
		$hostAndPort = $this->dsn['host'] . ':' . $this->dsn['port'];		
		// @todo consider using @ (do some testing) and pass error messages to exception
		// instead. Also might try playing with `print_r(error_get_last());`
		$this->connection = mysql_connect($hostAndPort, $this->dsn['user'], $this->dsn['pass'], true, $this->dsn['client_flags']);
		if (!$this->connection) {
			throw new As_DatabaseConnectException('mysql', $this->dsn["host"], $this->dsn["port"], $this->dsn["user"]);
		}
		
		// select default DB if provided
		if (isset($this->dsn['db_name'])) {
			$this->selectDb($this->dsn['db_name']);
		} else {
			$this->dbName = null;
		}
	}
	
	protected function disconnect()
	{
		if ($this->connection !== false)
		{
			mysql_close($this->connection);
		}
	}
	
	protected function _selectDb($dbName)
	{
		return mysql_select_db($dbName, $this->connection);
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
		
		return '"' . mysql_real_escape_string($value, $this->connection) . '"';
	}
	
	public function backtick($value)
	{
		return '`' . str_replace('`', '', $value) . '`';
	}
	
	protected function _query($sql)
	{
		$result = mysql_query($sql, $this->connection);
		if (!$result) {
			return $result;
		}
		return new As_MysqlDatabaseResult($result);
	}
	
	public function getNumAffectedRows()
	{
		return mysql_affected_rows($this->connection);
	}
	
	public function getLastInsertId()
	{
		return mysql_insert_id($this->connection);
	}
	
	/**
	 * Number of found rows from the last run query.
	 * 
	 * Make sure to put SQL_CALC_FOUND_ROWS immediately after the SELECT in
	 * order for this to work.
	 * 
	 * @return int
	 **/
	public function getNumFoundRows()
	{
		return $this->getResult('SELECT FOUND_ROWS()');
	}
	
	public function getError()
	{
		if ($this->connection) {
			if ($this->inTransaction) {
				return 'Transaction Failed with mysql_error: ' . mysql_error($this->connection);
			} else {
				return mysql_error($this->connection);
			}
		}
	}
	
	public function startTransaction()
	{
		$this->query('SET AUTOCOMMIT = 0');
		$this->inTransaction = true;
	}
	
	public function commit()
	{
		$this->query('COMMIT');
		$this->query('SET AUTOCOMMIT = 1');
		$this->inTransaction = false;
	}
	
	public function rollback()
	{
		$this->query('ROLLBACK');
		$this->query('SET AUTOCOMMIT = 1');
		$this->inTransaction = false;
	}
	
}

?>