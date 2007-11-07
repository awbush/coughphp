<?php

/**
 * SchemaDatabase contains information about one database (its tables).
 *
 * @package schema
 * @author Anthony Bush
 **/
class SchemaDatabase {
	
	protected $server = null; // reference to parent object
	protected $databaseName = null;
	protected $tables = array();
	
	// Getters
	
	public function getSchema() {
		return $this->server;
	}
	
	public function getServer() {
		return $this->server;
	}
	
	public function getDatabaseName() {
		return $this->databaseName;
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
	
	// Setters
	
	public function setServer($server) {
		$this->server = $server;
	}
	
	public function setDatabaseName($databaseName) {
		$this->databaseName = $databaseName;
	}
	
	public function addTable($table) {
		$this->tables[$table->getTableName()] = $table;
	}
	
	
}

?>