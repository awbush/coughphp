<?php

/**
 * SchemaTable contains information about one table (its columns).
 *
 * @package schema
 * @author Anthony Bush
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
	 *     'ref_database' => 'ref_database_name', // optional, only required if the database that the ref_table is on a different database than the local table
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
	
	public function getSchema() {
		return $this->getDatabase()->getSchema();
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
	
	public function hasPrimaryKey() {
		$primaryKey = $this->getPrimaryKey();
		if (empty($primaryKey)) {
			return false;
		} else {
			return true;
		}
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
	public function addForeignKey(SchemaForeignKey $foreignKey) {
		if (!$this->hasForeignKey($foreignKey)) {
			$this->foreignKeys[] = $foreignKey;
		}
	}
	
	/**
	 * Check if the specified foreign key already exists (by comparing it's
	 * local key name)
	 *
	 * @return bool - true if the specified foreign key already exists, false if not
	 * @author Anthony Bush
	 **/
	public function hasForeignKey(SchemaForeignKey $foreignKeyNeedle) {
		foreach ($this->foreignKeys as $foreignKey) {
			if ($foreignKey->getLocalKeyName() == $foreignKeyNeedle->getLocalKeyName()) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Returns the existing foreign key (with same local key name) if one
	 * exists, otherwise returns null.
	 *
	 * @return mixed - SchemaForeignKey if found, null if not.
	 * @author Anthony Bush
	 **/
	public function getForeignKey(SchemaForeignKey $foreignKeyNeedle) {
		foreach ($this->foreignKeys as $foreignKey) {
			if ($foreignKey->getLocalKeyName() == $foreignKeyNeedle->getLocalKeyName()) {
				return $foreignKey;
			}
		}
		return null;
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