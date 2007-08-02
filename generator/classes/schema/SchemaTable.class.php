<?php

/**
 * Take a tableName and database link and provide access to the tables
 * properties, such as its columns.
 * 
 * @package Schema
 * @author Anthony Bush
 * @copyright Anthony Bush (http://anthonybush.com/), 2006-08-26
 **/
class SchemaTable {
	
	protected $database = null; // reference to parent
	protected $tableName = null;
	protected $columns = array();
	
	/**
	 * Format of
	 * array(
	 *     'local_key' => array('col_name1'[, 'col_name2']*),
	 *     'ref_table' => 'ref_table_name',
	 *     'ref_key' => array('ref_col_name1'[, 'ref_col_name2']*)
	 * )
	 *
	 * @var array
	 **/
	protected $foreignKeys = array();
	
	/**
	 * one-to-one relationships
	 *
	 * @var array
	 **/
	protected $hasOneRelationships = array();
	
	/**
	 * one-to-many relationships
	 *
	 * @var array
	 **/
	protected $hasManyRelationships = array();
	
	/**
	 * many-to-many relationships (habtm = has and belongs to many)
	 *
	 * @var array
	 **/
	protected $habtmRelationships = array();
	
	// Getters
	
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
	
	public function getForeignKeys() {
		return $this->foreignKeys;
	}
	
	public function getHasOneRelationships() {
		return $this->hasOneRelationships;
	}
	
	public function getHasManyRelationships() {
		return $this->hasManyRelationships;
	}
	
	public function getHabtmRelationships() {
		return $this->habtmRelationships;
	}

	// Setters
	
	public function setDatabase($database) {
		$this->database = $database;
	}
	
	public function setTableName($tableName) {
		$this->tableName = $tableName;
	}
	
	public function addColumn($column) {
		$this->columns[$column->getColumnName()] = $column;
	}
	
	/**
	 * Add a foreign key hash to the table.
	 * 
	 * @return void
	 * @throws Exception
	 * @see $foreignKeys
	 **/
	public function addForeignKey($foreignKey) {
		if (isset($foreignKey['local_key']) && isset($foreignKey['ref_table']) && isset($foreignKey['ref_key'])) {
			$this->foreignKeys[] = $foreignKey;
		} else {
			throw new Exception('First argument must be a foreign key (hash) containing local_key, ref_table, and ref_key. See documentation for SchemaTable::$foreignKeys.');
		}
	}

	public function addHasOneRelationship($hasOneRelationship) {
		$this->hasOneRelationships[] = $hasOneRelationship;
	}
	
	public function addHasManyRelationship($hasManyRelationship) {
		$this->hasManyRelationships[] = $hasManyRelationship;
	}
	
	public function addHabtmRelationshis($habtmRelationship) {
		$this->habtmRelationships[] = $habtmRelationship;
	}	
	
}

?>