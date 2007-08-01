<?php

abstract class AbstractDatabase {
	protected $dbLink = null;
	protected $dbName = null;
	protected $server = null; // reference to parent object
	protected $tables = array();
	
	public function __construct($dbName, $dbLink = null, $server = null) {
		$this->dbName = $dbName;
		$this->dbLink = $dbLink;
		$this->server = $server;
	}
	
	public function getServer() {
		return $this->server;
	}
	
	public function getDatabaseName() {
		return $this->dbName;
	}
	
	public function getTables() {
		return $this->tables;
	}
	
	public function getTable($tableName) {
		if (isset($this->tables[$tableName])) {
			return $this->tables[$tableName];
		} else {
			return null;
		}
	}
	
	public function loadTables() {
		$this->selectDb($this->dbName);
		$tableNames = $this->getTableNames();
		
		$this->tables = array();
		foreach ($tableNames as $tableName) {
			$this->loadTable($tableName);
		}
		return $this->tables;
	}
	
	abstract public function loadTable($tableName);
	
	abstract public function getTableNames();
	
	abstract public function selectDb($dbName);
	
}

?>