<?php

/**
 * Implements the interface for the Database Driver.
 *
 * @package schema_generator
 * @author Anthony Bush
 **/
class MysqliDatabase extends SchemaDatabase implements DriverDatabase {
	
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
		$table = new MysqliTable($tableName, $this->dbLink, $this);
		$table->loadColumns();
		$this->tables[$tableName] = $table;
		return $table;
	}
	
	public function getAvailableTableNames() {
		$this->selectDb($this->databaseName);
		$sql = 'SHOW TABLES';
		if (is_null($this->dbLink)) {
			$result = mysqli_query($sql);
		} else {
			$result = mysqli_query($this->dbLink, $sql);
		}
		if ( ! $result) {
			$this->generateError('Invalid query');
		}
		$values = array();
		while ($record = mysqli_fetch_array($result, MYSQL_NUM)) {
			$values[] = $record[0];
		}
		return $values;
	}
	
	public function selectDb($dbName) {
		$db_selected = mysqli_select_db($this->dbLink, $dbName);
		if ( ! $db_selected) {
			$this->generateError("Can't select database");
		}
	}
	
	protected function generateError($msg) {
		if (is_null($this->dbLink)) {
			throw new Exception($msg . ': ' . mysqli_error());
		} else {
			throw new Exception($msg . ': ' . mysqli_error($this->dbLink));
		}
	}
	
}

?>