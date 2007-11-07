<?php

/**
 * Implements the interface for the Database Driver.
 *
 * @package schema_generator
 * @author Anthony Bush
 **/
class MysqlDatabase extends SchemaDatabase implements DriverDatabase {
	
	protected $dbLink = null;
	
	public function __construct($dbName, $dbLink = null, $server = null) {
		$this->setDatabaseName($dbName);
		$this->setServer($server);
		$this->dbLink = $dbLink;
	}
	
	public function loadTables() {
		$tableNames = $this->getAvailableTableNames();
		
		$this->tables = array();
		foreach ($tableNames as $tableName) {
			$this->loadTable($tableName);
		}
	}
	
	/**
	 * Load a specific database table into memory.
	 * 
	 * @param string $tableName
	 * @return DriverTable
	 * @author Anthony Bush
	 **/
	public function loadTable($tableName) {
		$table = new MysqlTable($tableName, $this->dbLink, $this);
		$table->loadColumns();
		$this->tables[$tableName] = $table;
		return $table;
	}
	
	public function getAvailableTableNames() {
		$this->selectDb($this->databaseName);
		$sql = 'SHOW TABLES';
		if (is_null($this->dbLink)) {
			$result = mysql_query($sql);
		} else {
			$result = mysql_query($sql, $this->dbLink);
		}
		if ( ! $result) {
			$this->generateError('Invalid query');
		}
		$values = array();
		while ($record = mysql_fetch_array($result, MYSQL_NUM)) {
			$values[] = $record[0];
		}
		return $values;
	}
	
	public function selectDb($dbName) {
		$db_selected = mysql_select_db($dbName, $this->dbLink);
		if ( ! $db_selected) {
			$this->generateError("Can't select database");
		}
	}
	
	protected function generateError($msg) {
		if (is_null($this->dbLink)) {
			throw new Exception($msg . ': ' . mysql_error());
		} else {
			throw new Exception($msg . ': ' . mysql_error($this->dbLink));
		}
	}
	
}

?>