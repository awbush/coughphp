<?php

// works for one-to-one and one-to-many, but what about many-to-many?
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
	
	public function getLocalRefObjectName() {
		return $this->localRefObjectName;
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
	
	public function setLocalRefObjectName($localRefObjectName) {
		$this->localRefObjectName = $localRefObjectName;
	}
	
	public function setLocalKey($localKey) {
		$this->localKey = $localKey;
	}	
	
}


class SchemaRelationshipHasOne extends SchemaRelationship {
	
}
class SchemaRelationshipHasMany extends SchemaRelationship {
	
}
class SchemaRelationshipHabtm extends SchemaRelationship {
	protected $joinTable = null;
	protected $joinObjectName = null;
	protected $joinKey = null;
	
	protected $joinTable = null;
	protected $joinKey = null;
	
	
	
}


?>