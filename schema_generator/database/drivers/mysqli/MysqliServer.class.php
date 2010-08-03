<?php

/**
 * Implements the interface for Server Driver, which:
 * 
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
 * @package schema_generator
 * @author Anthony Bush
 **/
class MysqliServer extends Schema implements DriverServer {
	
	protected $dbLink = null;
	protected $connected = false;
	
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
		$port = '';
		if (isset($dsn['port'])) {
			$port = $dsn['port'];
		}
		
		$this->dbLink = @mysqli_connect($host, $user, $pass, '', $port);
		if ( ! $this->dbLink) {
			throw new Exception('Could not connect to MySQL server: ' . mysqli_error() . ' | Using ' . print_r($dsn, true));
		} else {
			$this->connected = true;
		}
	}
	
	public function __destruct() {
		if ($this->connected) {
			mysqli_close($this->dbLink);
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
		$dbNames = $this->getAvailableDatabaseNames();
		
		$this->databases = array();
		foreach ($dbNames as $dbName) {
			$this->loadDatabase($dbName)->loadTables();
		}
	}
	
	/**
	 * Load a specific database schema into memory.
	 * 
	 * @param string $dbName
	 * @return DriverDatabase
	 * @author Anthony Bush
	 **/
	public function loadDatabase($dbName) {
		$this->databases[$dbName] = new MysqliDatabase($dbName, $this->dbLink, $this);
		return $this->databases[$dbName];
	}
	
	/**
	 * Get a list of available database names.
	 *
	 * @return array of strings
	 * @author Anthony Bush
	 **/
	public function getAvailableDatabaseNames() {
		$result = mysqli_query( $this->dbLink, 'SHOW DATABASES');
		if ( ! $result) {
			throw new Exception('Invalid query: ' . mysqli_error($this->dbLink));
		}
		$values = array();
		while ($record = mysqli_fetch_array($result, MYSQL_NUM)) {
			$values[] = $record[0];
		}
		return $values;
	}
	
}

?>