<?php

class AbstractColumn {
	protected $columnName = null;
	protected $isNullAllowed = null;
	protected $defaultValue = null;
	protected $type = null;
	protected $size = null;
	protected $isPrimaryKey = null;
	protected $table = null;
	
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
	public function getTable() {
		return $this->table;
	}
	
}

?>