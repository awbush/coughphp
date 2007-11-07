<?php

/**
 * SchemaColumn contains information about one column (its attributes).
 *
 * @package schema
 * @author Anthony Bush
 **/
class SchemaColumn {
	
	protected $table = null; // reference to parent object
	protected $columnName = null;
	protected $isNullAllowed = null;
	protected $defaultValue = null;
	protected $type = null;
	protected $size = null;
	protected $isPrimaryKey = null;
	
	// Getters
	
	public function getSchema() {
		return $this->getDatabase()->getSchema();
	}
	
	public function getDatabase() {
		return $this->getTable()->getDatabase();
	}
	
	public function getTable() {
		return $this->table;
	}
	
	public function getColumnName() {
		return $this->columnName;
	}
	
	public function isNullAllowed() {
		return $this->isNullAllowed;
	}
	
	public function getDefaultValue() {
		return $this->defaultValue;
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function getSize() {
		return $this->size;
	}
	
	public function isPrimaryKey() {
		return $this->isPrimaryKey;
	}
	
	// Setters
	
	public function setTable($table) {
		$this->table = $table;
	}
	
	public function setColumnName($columnName) {
		$this->columnName = $columnName;
	}
	
	public function setIsNullAllowed($isNullAllowed) {
		$this->isNullAllowed = $isNullAllowed;
	}
	
	public function setDefaultValue($defaultValue) {
		$this->defaultValue = $defaultValue;
	}
	
	public function setType($type) {
		$this->type = $type;
	}
	
	public function setSize($size) {
		$this->size = $size;
	}
	
	public function setIsPrimaryKey($isPrimaryKey) {
		$this->isPrimaryKey = $isPrimaryKey;
	}
		
}

?>