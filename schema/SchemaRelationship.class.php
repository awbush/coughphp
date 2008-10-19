<?php

/**
 * The base SchemaRelationship class contains and provides accessors to the
 * local table object and key and the reference (foreign) table object and key.
 * 
 * @package schema
 * @author Anthony Bush
 * @todo 2007-10-24/AWB: consider deprecating the "ObjectName" stuff as it doesn't get set at the schema level, but instead at the generation level (e.g. a config function might take one of the objects and return the "ObjectName" but it is not pre-filled here)
 **/
class SchemaRelationship {
	protected $refTable = null;
	protected $refObjectName = null;
	protected $refKey = null;
	
	protected $localTable = null;
	protected $localRefObjectName = null;
	protected $localKey = null;

	// Getters
	
	public function getRefTable() {
		return $this->refTable;
	}
	
	public function getRefTableName() {
		return $this->getRefTable()->getTableName();
	}
	
	public function getRefObjectName() {
		return $this->refObjectName;
	}
	
	public function getRefKey() {
		return $this->refKey;
	}
	
	public function getLocalTable() {
		return $this->localTable;
	}
	
	public function getLocalTableName() {
		return $this->getLocalTable()->getTableName();
	}
	
	public function getLocalObjectName() {
		return $this->localObjectName;
	}
	
	public function getLocalKey() {
		return $this->localKey;
	}
	
	// Setters
	
	public function setRefTable($table) {
		$this->refTable = $table;
	}
	
	public function setRefKey($key) {
		$this->refKey = $key;
	}
	
	public function setRefObjectName($objectName) {
		$this->refObjectName = $objectName;
	}
	
	public function setLocalTable($localTable) {
		$this->localTable = $localTable;
	}
	
	public function setLocalObjectName($localObjectName) {
		$this->localObjectName = $localObjectName;
	}
	
	public function setLocalKey($localKey) {
		$this->localKey = $localKey;
	}
	
	/**
	 * Returns false if all keys are NOT NULL, true otherwise.
	 * 
	 * @return boolean
	 **/
	public function isKeyNullable($keySet) {
		foreach ($keySet as $key) {
			if ($key->isNullAllowed()) {
				// A single key allows NULL => the entire key is not guaranteed to be NOT NULL.
				return true;
			}
		}
		// All keys are NOT NULL
		return false;
	}
}

?>