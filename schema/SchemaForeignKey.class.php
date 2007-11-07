<?php

/**
 * undocumented class
 *
 * @package schema
 * @author Anthony Bush
 **/
class SchemaForeignKey {
	
	protected $localDatabaseName = '';
	protected $localTableName = '';
	protected $localKeyName = array();
	protected $localObjectName = '';
	protected $refDatabaseName = '';
	protected $refTableName = '';
	protected $refKeyName = array();
	protected $refObjectName = '';
	protected $isLinked = false;

	public function __construct($data = array()) {
		if (isset($data['local_database_name'])) {
			$this->setLocalDatabaseName($data['local_database_name']);
		}
		if (isset($data['local_table_name'])) {
			$this->setLocalTableName($data['local_table_name']);
		}
		if (isset($data['local_key_name'])) {
			$this->setLocalKey($data['local_key_name']);
		}
		if (isset($data['local_object_name'])) {
			$this->setLocalObjectName($data['local_object_name']);
		}
		if (isset($data['ref_database_name'])) {
			$this->setRefDatabaseName($data['ref_database_name']);
		}
		if (isset($data['ref_table_name'])) {
			$this->setRefTable($data['ref_table_name']);
		}
		if (isset($data['ref_key_name'])) {
			$this->setRefKey($data['ref_key_name']);
		}
		if (isset($data['ref_object_name'])) {
			$this->setRefObjectName($data['ref_object_name']);
		}
	}
	
	public function getLocalDatabaseName() {
		return $this->localDatabaseName;
	}
	public function getLocalTableName() {
		return $this->localTableName;
	}
	public function getLocalKeyName() {
		return $this->localKeyName;
	}
	public function getLocalObjectName() {
		return $this->localObjectName;
	}
	public function hasLocalObjectName() {
		return !empty($this->localObjectName);
	}
	public function getRefDatabaseName() {
		return $this->refDatabaseName;
	}
	public function hasRefDatabaseName() {
		return !empty($this->refDatabaseName);
	}
	public function getRefTableName() {
		return $this->refTableName;
	}
	public function getRefKeyName() {
		return $this->refKeyName;
	}
	public function getRefObjectName() {
		return $this->refObjectName;
	}
	public function hasRefObjectName() {
		return !empty($this->refObjectName);
	}
	public function isLinked() {
		return $this->isLinked;
	}

	public function setLocalDatabaseName($localDatabaseName) {
		$this->localDatabaseName = $localDatabaseName;
	}
	public function setLocalTableName($localTableName) {
		$this->localTableName = $localTableName;
	}
	public function setLocalKeyName($localKeyName) {
		$this->localKeyName = $localKeyName;
	}
	public function setLocalObjectName($localObjectName) {
		$this->localObjectName = $localObjectName;
	}
	public function setRefDatabaseName($refDatabaseName) {
		$this->refDatabaseName = $refDatabaseName;
	}
	public function setRefTableName($refTableName) {
		$this->refTableName = $refTableName;
	}
	public function setRefKeyName($refKeyName) {
		$this->refKeyName = $refKeyName;
	}
	public function setRefObjectName($refObjectName) {
		$this->refObjectName = $refObjectName;
	}
	public function setIsLinked($isLinked) {
		$this->isLinked = $isLinked;
	}
	
}

?>
