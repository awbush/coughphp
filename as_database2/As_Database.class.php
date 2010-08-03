<?php

/**
 * Base database object that driver-specific database classes shall extend.
 * 
 * This file is released under the FreeBSD license.
 * 
 * @package as_database
 **/
abstract class As_Database
{
	/**
	 * Connects to server specified via DSN information.
	 * 
	 * @return void
	 * @throws As_DatabaseConnectException
	 **/
	abstract protected function connect();
	
	/**
	 * Disconnects from server
	 *
	 * @return void
	 **/
	abstract protected function disconnect();
	
	/**
	 * Select given database.
	 * 
	 * @param string $dbName
	 * @return bool true on success, false on failure
	 **/
	abstract protected function _selectDb($dbName);

	/**
	 * Runs the query and returns the DAL-specific result object.
	 * 
	 * @param string $sql
	 * @return As_DatabaseResult
	 **/
	abstract protected function _query($sql);
	
	/**
	 * Escape and quote values for use in queries.
	 * 
	 * @param string $value
	 * @return string
	 **/
	abstract public function quote($value);
	
	/**
	 * Backtick reserved words for use in queries.
	 * 
	 * @param string $value
	 * @return string
	 **/
	abstract public function backtick($value);
	
	/**
	 * Returns the error message from the last query run.
	 *
	 * @return string
	 **/
	abstract public function getError();
	
	/**
	 * Number of affected rows in previous operation.
	 *
	 * @return int
	 **/
	abstract public function getNumAffectedRows();
	
	/**
	 * ID generated for an AUTO_INCREMENT column by the previous INSERT query.
	 *
	 * @return int
	 **/
	abstract public function getNumFoundRows();
	
	/**
	 * Number of found rows from the last run query.
	 * 
	 * @return int
	 **/
	abstract public function getLastInsertId();
	
	/**
	 * Starts a transaction.
	 * 
	 * Example Usage:
	 * 
	 *     <code>
	 *     $db->startTransaction();
	 *     try {
	 *         $db->query($sql1);
	 *         $db->query($sql2);
	 *         $db->query($sql3);
	 *         $db->commit();
	 *     } catch (Exception $e) {
	 *         $db->rollback();
	 *     }
	 *     </code>
	 *
	 * @return void
	 * @see commit(), rollback(), $inTransaction
	 **/
	abstract public function startTransaction();
	
	/**
	 * Commit a transaction.
	 *
	 * @return void
	 * @see startTransaction(), rollback(), $inTransaction
	 **/
	abstract public function commit();
	
	/**
	 * Rollback a non-committed transaction.
	 *
	 * @return void
	 * @see startTransaction(), commit(), $inTransaction
	 **/
	abstract public function rollback();
	
	/**
	 * Resource from *_connect() calls
	 *
	 * @var mixed
	 **/
	protected $connection = null;
	
	/**
	 * Name of currently selected database
	 *
	 * @var string|null
	 **/
	protected $dbName = null;
	
	/**
	 * Whether or not we are currently in a transaction.
	 *
	 * @var boolean
	 * @see startTransaction(), commit(), rollback()
	 **/
	protected $inTransaction = false;
	
	/**
	 * An array of observers
	 *
	 * @var array
	 * @see addObserver()
	 **/
	protected $observers = array();
	
	protected $lastQueryTime = null;
	protected $lastQuery = null;
	protected $lastQueryResult = null;
	protected $lastParams = null;
	protected $lastTypes = null;
	
	/**
	 * Perform any necessary bookkeeping after unserialization
	 *
	 * Reestablish the db connection after we are unserialized
	 * 
	 */
	public function __wakeup()
    {
		$this->connection = null;
		$this->connect();
	}
	
	
	/**
	 * returns the correct database abstraction object
	 * 
	 * Example:
	 * 
	 *     <code>
	 *     $db = As_Database::constructByConfig(array(
	 *         'driver' => 'mysql',
	 *         'host' => 'localhost',
	 *         'user' => 'nobody',
	 *         'pass' => '',
	 *         'port' => '3306',
	 *         'client_flags' => MYSQL_CLIENT_COMPRESS,
	 *     ));
	 *     $db->query('SELECT * FROM some_table LIMIT 1');
	 *     </code>
	 *
	 * @param array $dbConfig
	 * @return As_Database
	 **/
	public static function constructByConfig($dbConfig)
	{
		if (isset($dbConfig['driver'])) {
			$driver = $dbConfig['driver'];
		} else {
			$driver = 'mysql';
		}
		$className = 'As_' . ucfirst($driver) . 'Database';
		$classPath = dirname(__FILE__) . '/' . $driver . '/';
		
		require_once($classPath . $className . '.class.php');
		require_once($classPath . $className . 'Result.class.php');
		
		return call_user_func(array($className, 'constructByConfig'), $dbConfig);
	}
	
	public function __construct(array $dsn = array())
	{
		$this->dsn = $dsn + $this->dsn;
		$this->connect();
	}
	
	/**
	 * Clean up (rollback transactions if in one)
	 *
	 * @return void
	 **/
	public function __destruct()
	{
		if ($this->inTransaction) {
			$this->rollback();
		}
		$this->disconnect();
	}
	
	/**
	 * Alias method for {@link startTransaction()}
	 *
	 * @return void
	 **/
	public function beginTransaction()
	{
		$this->startTransaction();
	}
	
	/**
	 * Whether or not database connection is currently in a transaction
	 *
	 * @return bool
	 **/
	public function isInTransaction()
	{
		return $this->inTransaction;
	}
	
	/**
	 * Select a database
	 * 
	 * @param string $dbName
	 * @return void
	 * @throws As_DatabaseException
	 **/
	public function selectDb($dbName)
	{
		if ($dbName != $this->dbName) {
			if (!$this->_selectDb($dbName)) {
				throw new As_DatabaseException('Unable to select database ' . $dbName . ': ' . $this->getError());
			}
			$this->dbName = $dbName;
		}
	}
	
	/**
	 * Currently selected database name
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function getDbName()
	{
		return $this->dbName;
	}
	
	/**
	 * Query database
	 * 
	 * @param string $sql
	 * @return As_DatabaseResult
	 * @throws As_DatabaseQueryException
	 **/
	public function query($sql)
	{
		$this->lastQuery = (string)$sql;
		$this->lastParams = null;
		$this->lastTypes = null;

		
		$start = microtime(true);
		$this->lastQueryResult = $this->_query($this->lastQuery);
		$this->lastQueryTime = microtime(true) - $start;

		
		if (!$this->lastQueryResult)
		{
			throw new As_DatabaseQueryException($this->getError(), $this->lastQuery);
		}
		
		foreach ($this->observers as $observer)
		{
			$observer->notify('query', $this);
		}
		
		return $this->lastQueryResult;
	}
	
	public function queryPreparedStmt($sql, $params, $types = '')
	{
		$this->lastQuery = (string)$sql;
		$this->lastParams = $params;
		$this->lastTypes = $types;
		
		if (count($params) == 0)
		{
			return $this->query($sql);
		}
		
		$start = microtime(true);
		$this->lastQueryResult = $this->_queryPreparedStmt($this->lastQuery, $this->lastParams, $this->lastTypes);
		$this->lastQueryTime = microtime(true) - $start;
		
		if (!$this->lastQueryResult)
		{
			throw new As_DatabaseQueryException($this->getError(), $this->lastQuery, $this->lastParams, $this->lastTypes);
		}
		
		foreach ($this->observers as $observer)
		{
			$observer->notify('query', $this);
		}
		
		return $this->lastQueryResult;
	}
	
	public function canQueryPreparedStmt()
	{
		return false;
	}
	
	/**
	 * Execute a query and return the # of affected rows.
	 *
	 * @return int
	 **/
	public function execute($sql)
	{
		$this->query($sql);
		return $this->getNumAffectedRows();
	}
	
	/**
	 * Execute a database query and retrieve the value of the first field in the result
	 * 
	 * @param string $sql sql statement to execute
	 * @return mixed the value of the first field in the result (or false if none exists?)
	 * @todo verify this shouldn't be a method that returns the last result object
	 */
	public function getResult($sql)
	{
		$result = $this->query($sql);
		if ($result->getNumRows()) {
			return $result->getResult(0);
		} else {
			return false;
		}
	}
	
	public function getEqualityOperator($value)
	{
		if ($value === null) {
			return ' IS ';
		} else {
			return ' = ';
		}
	}
	
	public function getInequalityOperator($value)
	{
		if ($value === null) {
			return ' IS NOT ';
		} else {
			return ' != ';
		}
	}
	
	public function getInsertQuery()
	{
		return new As_InsertQuery($this);
	}
	
	public function getSelectQuery()
	{
		return new As_SelectQuery($this);
	}
	
	public function getUpdateQuery()
	{
		return new As_UpdateQuery($this);
	}
	
	public function getDeleteQuery()
	{
		return new As_DeleteQuery($this);
	}
	
	/**
	 * Add observer
	 * 
	 * @param mixed $observer any object with a notify($eventType, $db) method.
	 * @return void
	 **/
	public function addObserver($observer)
	{
		$this->observers[] = $observer;
	}
	
	/**
	 * Remove observer
	 * 
	 * @param mixed $observer
	 * @return bool whether observer was found and removed or not.
	 **/
	public function removeObserver($observerToRemove)
	{
		foreach ($this->observers as $key => $observer)
		{
			if ($observer === $observerToRemove) {
				unset($this->observers[$key]);
				return true;
			}
		}
		return false;
	}
	
	/**
	 *
	 * Gets the observers for this object
	 *
	 * @return mixed array of observers that are interested in this object
	 **/
	
	public function getObservers()
	{
		return $this->observers;
	}
	
	public function getLastQueryTime()
	{
		return $this->lastQueryTime;
	}
	
	public function getLastQuery()
	{
		return $this->lastQuery;
	}
	
	public function getLastQueryResult()
	{
		return $this->lastQueryResult;
	}

	public function getLastParams()
	{
		return $this->lastParams;
	}
	
	public function getLastTypes()
	{
		return $this->lastTypes;
	}
	
	
}

?>