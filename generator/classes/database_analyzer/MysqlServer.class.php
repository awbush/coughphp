<?php

/**
 * Given connection credentials, it gives read-only access to seeing what
 * databases, tables, and columns are available.
 * 
 * Example usage:
 * 
 *     $server = new MysqlServer();
 *     $server->loadDatabases();
 *     print_r($server->getDatabases());
 * 
 * Example usage to get just one database:
 * 
 *     $server = new MysqlServer();
 *     $server->loadDatabase('db_name');
 *     print_r($dba->getDatabase('db_name'));
 * 
 * @package default
 * @author Anthony Bush
 * @copyright Anthony Bush (http://anthonybush.com/), 2006-08-26
 **/
class MysqlServer {
	protected $dbLink = null;
	protected $connected = false;
	protected $databases = array();
	
	/**
	 * Construct with a DSN, an array containing host, user, pass, and port (optional).
	 * 
	 * For example:
	 * 
	 *     $dsn = array(
	 *         'host' => '127.0.0.1',
	 *         'user' => 'nobody',
	 *         'pass' => '',
	 *         'port' => 3306
	 *     );
	 *     
	 *     $server = new MysqlServer($dsn);
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function __construct($dsn) {
		
		if (isset($dsn['host'])) {
			$host = $dsn['host'];
		} else {
			$host = '127.0.0.1';
		}
		
		if (isset($dsn['user'])) {
			$user = $dsn['user'];
		} else {
			$user = 'nobody';
		}
		
		if (isset($dsn['pass'])) {
			$pass = $dsn['pass'];
		} else {
			$pass = '';
		}
		
		if (isset($dsn['port'])) {
			$host .= ':' . $dsn['port'];
		}
		
		$this->dbLink = mysql_connect($host, $user, $pass);
		if ( ! $this->dbLink) {
			throw new Exception('Could not connect to MySQL server: ' . mysql_error($this->dbLink));
		} else {
			$this->connected = true;
		}
	}
	
	public function __destruct() {
		if ($this->connected) {
			mysql_close($this->dbLink);
		}
	}
	
	public function getDatabases() {
		return $this->databases;
	}
	
	public function getDatabase($dbName) {
		if (isset($this->databases[$dbName])) {
			return $this->databases[$dbName];
		} else {
			return null;
		}
	}
	
	/**
	 * Load all database schemas for the currently connected server into memory.
	 * (will only load databases that the user/pass has privileges to see)
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function loadDatabases() {
		$dbNames = $this->getDatabaseNames();
		
		$this->databases = array();
		foreach ($dbNames as $dbName) {
			$this->loadDatabase($dbName);
		}
	}
	
	/**
	 * Load a specific database schema into memory.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function loadDatabase($dbName) {
		$this->databases[$dbName] = new MysqlDatabase($dbName, $this->dbLink, $this);
		$this->databases[$dbName]->loadTables();
	}
	
	public function getDatabaseNames() {
		$result = mysql_query('SHOW DATABASES', $this->dbLink);
		if ( ! $result) {
			throw new Exception('Invalid query: ' . mysql_error($this->dbLink));
		}
		$values = array();
		while ($record = mysql_fetch_array($result, MYSQL_NUM)) {
			$values[] = $record[0];
		}
		return $values;
	}
	
}

?>