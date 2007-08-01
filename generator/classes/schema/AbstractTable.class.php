<?php

/**
 * Take a tableName and database link and provide access to the tables
 * properties, such as its columns.
 * 
 * @package default
 * @author Anthony Bush
 * @copyright Anthony Bush (http://anthonybush.com/), 2006-08-26
 **/
abstract class AbstractTable {
	protected $tableName = null;
	protected $dbLink = null;
	protected $database = null;
	protected $columns = array();
	
	public function __construct($tableName, $dbLink = null, $database = null) {
		$this->tableName = $tableName;
		$this->dbLink = $dbLink;
		$this->database = $database;
	}
	
	public function getDatabase() {
		return $this->database;
	}
	
	public function getTableName() {
		return $this->tableName;
	}
	
	public function getColumns() {
		return $this->columns;
	}
	
	public function getColumn($columnName) {
		if (isset($this->columns[$columnName])) {
			return $this->columns[$columnName];
		} else {
			return null;
		}
	}
	
	public function getPrimaryKey() {
		$primaryKey = array();
		foreach ($this->columns as $columnName => $column) {
			if ($column->isPrimaryKey()) {
				$primaryKey[$columnName] = $column;
			}
		}
		return $primaryKey;
	}
	
	abstract public function loadColumns();
	
}

?>