<?php

/**
 * This is a cleaned up version of the Database (MySQL at the moment) in use at
 * Academic Superstore.
 * 
 * Most notably, all the "doSomething" functions are now "something" and all the
 * backward compatibility cruft for Persistent have ben removed.
 * 
 * This file is released under the FreeBSD license.
 * 
 * @author Anthony Bush
 **/
class As_Database {
	// Error Types
	const ERROR_CONNECT = 1;
	const ERROR_DB_SELECT = 2;
	const ERROR_INSERT = 3;
	const ERROR_UPDATE = 4;
	const ERROR_QUERY = 5;

	// Query Types
	const UPDATE = 1;
	const INSERT = 2;

	protected $dbHost = null;
	protected $dbName = null;
	protected $dbUser = null;
	protected $dbPassword = null;
	protected $dbPort;
	protected $connection;
	protected $query;
	
	/**
	 * Whether or not we are currently in a transaction.
	 *
	 * @var boolean
	 * @see startTransaction(), commit(), rollback()
	 **/
	protected $inTransaction = false;
	
	/**
	 * Whether or not to log all queries (to variable)
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected $logQueries = false;
	
	/**
	 * Logged queries since logQueries was turned on
	 * 
	 * @return void
	 * @author Anthony Bush
	 **/
	protected $queryLog = array();
	
	public function __construct($dbName, $dbHost = 'localhost', $dbUser = 'nobody', $dbPassword = '', $dbPort = 3306) {
		$this->dbHost = $dbHost;
		$this->dbUser = $dbUser;
		$this->dbPassword = $dbPassword;
		$this->dbPort = $dbPort;

		$this->initConnection();
		$this->selectDB($dbName);
	}

	public function selectDB($dbName) {
		if ($dbName != $this->dbName) {
			if (!mysql_select_db($dbName,$this->connection)) {
				$this->generateError(self::ERROR_DB_SELECT);
			}
			$this->dbName = $dbName;
		}
	}

	public function initConnection() {
		$hostAndPort = $this->dbHost . ":" . $this->dbPort;		
		$this->connection = mysql_connect($hostAndPort, $this->dbUser, $this->dbPassword,TRUE);
		if (!$this->connection) {
			$this->generateError(self::ERROR_CONNECT);
		}
		$this->dbName = null;
	}
	
	public function getSecondsBehindMaster($master) {
		$result = $this->query('SHOW SLAVE STATUS');
		while ($row = $result->getRow()) {
			if ($row['Master_Host'] == $master) {
				return $row['Seconds_Behind_Master'];
			}
		}
		return null;
	}

	public function quote($value) {

		// Handle special PHP values and SQL Functions
		if ($value === null) {
			return 'NULL';
		} else if ($value === false) {
			return '0';
		} else if ($value === true) {
			return '1';
		} else if ($value instanceof As_SqlFunction) {
			return $value->getString();
		}

		return '"' . $this->escape($value) . '"';
	}

	public function escape($value) {
		if (get_magic_quotes_gpc()) {
			$value = stripslashes($value);
		}
		return mysql_real_escape_string($value, $this->connection);
	}
	
	public function query($sql) {
		$this->query = $sql;
		
		$start = microtime(true);
		$result = @mysql_query($sql,$this->connection);
		if (!$result) {
			$this->generateError(self::ERROR_QUERY);
		}
		$finish = microtime(true);
		if ($this->logQueries) {
			$this->queryLog[] = array('sql' => $sql, 'time' => ($finish - $start));
		}
		
		return new As_DatabaseResult($result);
	}
	
	/**
	 * result function
	 *
	 * Execute a database query and retrieve the value of the first field in the result
	 *
	 *
	 * @access		public
	 * @name		result
	 * @param		string $sql sql statement to execute
	 * @return		the value of the first field in the result (or false if none exists?)
	 */
	public function result($sql) {
		$result = $this->query($sql);
		if ($result->numRows()) {
			return $result->result(0);
		} else {
			return false;
		}
	}

	public function getAffectedRows() {
		return mysql_affected_rows($this->connection);
	}

	/**
	 * Perform an INSERT.
	 * This function is Persistent Compatible.
	 *
	 * @param string $tableName - the table name to INSERT INTO.
	 * @param array $fields - an array of field_name => field_value pairs to be inserted.
	 * @param array $quotes - an array of field_name => true/false pairs of which field_values should be quoted/escaped.;
	 * @param string $dbName - optional database name to insert into (for Persistent compatibility).
	 * @param string $escape - optional specification of whether or not to escape quoted values (for Persistent compatibility).
	 * @return mixed - last inserted ID
	 * @author Matt Schiros
	 * @author Anthony Bush
	 **/
	public function insert($tableName, $fields, $quotes = null, $dbName = '', $escape = true) {
		if ($dbName) {
			$this->selectDB($dbName);
		}

		$sql = "";
		$sql .= "INSERT INTO " . $this->dbName . "." . $tableName . " ";
		$fieldList = "( ";
		$valueList = "( ";
		foreach($fields as $key => $value) {
			$fieldList .= $key . ",";
			if (is_null($quotes) || (is_array($quotes) && isset($quotes[$key]) && $quotes[$key])) {
				$value = $this->quote($value);
			}
			$valueList .= $value . ',';
		}
		$valueList = substr_replace($valueList,'',-1) . ") ";
		$fieldList = substr_replace($fieldList,'',-1) . ") ";
		$sql .= $fieldList . " VALUES " . $valueList;
	
		$this->query($sql);
		return $this->getInsertID();
	}

	public function insertMultiple($tableName,$fields,$data,$quotes=NULL,$dbName='',$escape = true) {
		$fieldMapArray = array();
		$quoteMapArray = array();

		if ($dbName) {
			$this->selectDB($dbName);
		}
		$sql = "";
		$sql .= "INSERT INTO " . $this->dbName . "." . $tableName . " (";
		$x = 0;
		foreach($fields as $key => $value) {
			$fieldMapArray[$key] = $x;

			if(is_null($quotes) || (is_array($quotes) && isset($quotes[$key]) && $quotes[$key])) {
				$quoteMapArray[$x] = TRUE;
			}
			$sql .= $key . ",";
			$x++;
		}
		$sql = substr_replace($sql,'',-1) . ") VALUES ";

		foreach($data as $valueArray) {
			$sql .= "(";
			$sortedArray = array();
			foreach($valueArray as $key => $value) {
				$position = $fieldMapArray[$key];
				$sortedArray[$position] = $value;
			}
			foreach($sortedArray as $position => $value) {
				if(isset($quoteMapArray[$position]) && $quoteMapArray[$position])  {
					$value = $this->quote($value);
				}
			}
			$sql .= "),";
		}
		$sql = substr_replace($sql,'',-1);
		$this->query($sql);

	}

	public function replaceMultiple($tableName,$fields,$data,$quotes=NULL,$dbName='',$escape = true) {
		$fieldMapArray = array();
		$quoteMapArray = array();

		if ($dbName) {
			$this->selectDB($dbName);
		}
		$sql = "";
		$sql .= "REPLACE INTO " . $this->dbName . "." . $tableName . " (";
		$x = 0;
		foreach($fields as $key => $value) {
			$fieldMapArray[$key] = $x;
			if(is_null($quotes) || (is_array($quotes) && isset($quotes[$key]) && $quotes[$key])) {
				$quoteMapArray[$x] = TRUE;
			}

			$sql .= $key . ",";
			$x++;
		}
		$sql = substr_replace($sql,'',-1) . ") VALUES ";

		foreach($data as $valueArray) {
			$sql .= "(";
			$sortedArray = array();
			foreach($valueArray as $key => $value) {
				$position = $fieldMapArray[$key];
				$sortedArray[$position] = $value;
			}
			foreach($sortedArray as $position => $value) {
				if(isset($quoteMapArray[$position]) && $quoteMapArray[$position])  {
					$value = $this->quote($value);
				}
			}
			$sql .= "),";
		}
		$sql = substr_replace($sql,'',-1);
		$this->query($sql);

	}


	public function replace($tableName, $fields, $quotes=NULL) {
		$sql = "";
		$sql .= "REPLACE INTO " . $this->dbName . "." . $tableName . " ";
		$fieldList = "( ";
		$valueList = "( ";
		foreach($fields as $key => $value) {
				$fieldList .= $key . ",";
				if(is_null($quotes) || (is_array($quotes) && isset($quotes[$key]) && $quotes[$key]))  {
					$value = $this->quote($value);
				}
				$valueList .= $value . ',';
		}
		$valueList = substr_replace($valueList,'',-1) . ") ";
		$fieldList = substr_replace($fieldList,'',-1) . ") ";
		$sql .= $fieldList . " VALUES " . $valueList;
		$this->query($sql);

	}



	public function update($tableName, $fields, $quotes=NULL, $where, $whereQuotes = NULL, $dbName = '', $escape = true) {
		if (empty($where)) {
			throw new Exception('You must specify a non-null, non-empty array where condition. Otherwise you will update ALL data in the database. If that is intentional, pass array(1=>1) or right your own SQL and call query().');
		}
		if (empty($fields)) {
			return false;
		}
		// TODO: Finish making this backward compatible to Persistent. I'm only adding the $dbName fix. (see bug 4693)
		if ($dbName != '') {
			$this->selectDb($dbName);
		}

		$sql = "";
		$sql .= "UPDATE " . $this->dbName . "." . $tableName . " SET ";
		$sql .= $this->generateSet($fields, $quotes);
		$sql .= $this->generateWhere($where, $whereQuotes);

		$this->query($sql);
		return true;
	}

	public function insertOrCancel($tableName, $fields, $quotes=NULL, $where) {
		$sql = 'SELECT * FROM ' . $tableName . ' ' . $this->generateWhere($where);
		$result = $this->query($sql);
		if($result->getNumRows() > 0) {
			return FALSE;
		}
		else {
			return $this->insert($tableName,$fields,$quotes);
		}
	}

	public function insertOrUpdate($tableName, $fields, $quotes=NULL, $where) {
		$sql = 'SELECT * FROM ' . $this->dbName . '.' . $tableName . ' ' . $this->generateWhere($where);
		$result = $this->query($sql);
		if($result->getNumRows() > 0) {
			$this->update($tableName, $fields, $quotes, $where);
			$type = self::UPDATE;
		}
		else {
			$this->insert($tableName,$fields,$quotes);
			$type = self::INSERT;
		}

		return $type;
	}

	/**
	 * Like insertOrUpdate, except a WHERE clause is not required
	 *
	 * Using this function will keep DUPLICATE KEY errors at bay for when
	 * you don't have access to the primary key. If you DO have access to the
	 * primary key, you can use insertOrUpdate and pass in the key values
	 * as the $where parameter.
	 *
	 * @author Anthony Bush
	 * @since 2007-02-24
	 **/
	public function insertOnDupUpdate($tableName, $fields, $quotes=NULL) {
		$sets = $this->generateSet($fields, $quotes);
		$sql = 'INSERT INTO ' . $this->dbName . '.' . $tableName . ' SET ' . $sets . ' ON DUPLICATE KEY UPDATE ' . $sets;
		return $this->query($sql);
	}

	public function generateSet($fields, $quotes=NULL) {
		$setList = '';
		foreach($fields as $key => $value) {
			if (is_null($quotes) || (is_array($quotes) && isset($quotes[$key]) && $quotes[$key])) {
				$value = $this->quote($value);
			}
			$setList .= $key . ' = ' . $value . ',';
		}
		return substr_replace($setList,'',-1) . ' ';
	}

	public function generateWhere($where=array(), $quotes=NULL, $prefix = 'WHERE') {
		if(empty($where)) {
			return '';
		}
		else {
			$whereClause = $prefix . ' ';
			foreach($where as $key => $value) {
				if (is_null($quotes) || (is_array($quotes) && isset($quotes[$key]) && $quotes[$key])) {
					$value = $this->quote($value);
				}
				$whereClause .= $key . $this->getTestForMatch($value) . $value . ' AND ';
			}
			$whereClause = substr_replace($whereClause, '', -5);
		}
		return $whereClause;
	}

	public function getTestForMatch($value) {
		if ($value == 'NULL') {
			return ' IS ';
		} else {
			return ' = ';
		}
	}

	public function getTestForNonMatch($value) {
		if ($value == 'NULL') {
			return ' IS NOT ';
		} else {
			return ' != ';
		}
	}

	public function select($tableName,$fields=array(),$where=array()) {
		if (empty($fields)) {
			$selected = '*';
		} else if (is_array($fields)) {
			$selected = implode(', ',$fields);
		} else {
			// assume string
			$selected = $fields;
		}

		$sql = 'SELECT ' . $selected . ' FROM ' . $this->dbName . '.' . $tableName . ' ' . $this->generateWhere($where);
		$result = $this->query($sql);
		return $result;
	}

	public function delete($tableName, $where) {
		if (empty($where)) {
			throw new Exception('You must specify a non-null, non-empty array where condition. Otherwise you will delete ALL data in table ' . $tableName . '. If that is intentional, pass array(1=>1) or right your own SQL and call query().');
		}
		$sql = 'DELETE FROM ' . $tableName . ' ' . $this->generateWhere($where);
		$this->query($sql);
	}

	public function getInsertID() {
		return mysql_insert_id($this->connection);
	}
	
	/**
	 * Starts a transaction.
	 * 
	 * Example Usage:
	 * 
	 *     $db->startTransaction();
	 *     try {
	 *         $db->query($sql1);
	 *         $db->query($sql2);
	 *         $db->query($sql3);
	 *         $db->commit();
	 *     } catch (Exception $e) {
	 *         $db->rollback();
	 *     }
	 *
	 * @return void
	 * @author Anthony Bush
	 * @see commit(), rollback(), $inTransaction
	 **/
	public function startTransaction() {
		$this->query('SET AUTOCOMMIT = 0');
		// Ensure that errors are always thrown.
		$this->inTransaction = true;
	}
	
	/**
	 * Commit a transaction.
	 *
	 * @return void
	 * @author Anthony Bush
	 * @see startTransaction(), rollback(), $inTransaction
	 **/
	public function commit() {
		$this->query('COMMIT');
		$this->query('SET AUTOCOMMIT = 1');
		$this->inTransaction = false;
	}
	
	public function isInTransaction() {
		return $this->inTransaction;
	}
	
	/**
	 * Rollback a non-committed transaction.
	 *
	 * @return void
	 * @author Anthony Bush
	 * @see startTransaction(), commit(), $inTransaction
	 **/
	public function rollback() {
		$this->query('ROLLBACK');
		$this->query('SET AUTOCOMMIT = 1');
		$this->inTransaction = false;
	}
	
	protected function generateError($errorType) {
		
		if ($this->inTransaction) {
			$sqlError = 'Transaction Failed with mysql_error: ' . mysql_error($this->connection);
		} else {
			$sqlError = mysql_error($this->connection);
		}
		
		switch($errorType) {
			case self::ERROR_CONNECT:
				$type = "Database Connect";
				$sql = "[SQL N/A]";
				break;

			case self::ERROR_DB_SELECT:
				$type = "Selecting Database";
				$sql = "[SQL N/A]";
				break;

			case self::ERROR_INSERT:
				$type = "Inserting Data";
				$sql = $this->query;
				break;

			case self::ERROR_UPDATE:
				$type = "Updating Data";
				$sql = $this->query;
				break;

			case self::ERROR_QUERY:
			default:
				$type = "Query";
				$sql = $this->query;
				break;
		}
		
		throw new Exception('As_Database Error [' . $type . ']: ' . $sqlError);
	}

	public function getQuery() {
		return $this->query;
	}
	
	public function startLoggingQueries() {
		$this->logQueries = true;
	}
	
	public function stopLoggingQueries() {
		$this->logQueries = false;
	}
	
	public function getQueryLog() {
		return $this->queryLog;
	}
	
	public function getQueryLogTime() {
		$time = 0.0;
		foreach ($this->queryLog as $query) {
			$time += $query['time'];
		}
		return $time;
	}
	
}

?>