<?php

/**
 * undocumented class DynaCoughObject
 **/
class DynaCoughObject extends CoughObject {
	
	protected function defineCore() {
		$this->init_dbName = '';
		$this->init_tableName = '';
		
		if (count($this->init_columns) == 0 || $this->init_primaryKey == '') {
			$this->checkInitColumns();
		}
	}
	public function setInitColumns($columns=array()) {
		$this->init_columns = $columns;
	}
	public function setPrimaryKey($key) {
		$this->init_primaryKey = $key;
	}
	public function setNameColumn($nameColumn) {
		$this->init_nameColumn = $nameColumn;
	}
	
	public function checkInitColumns() {
		$p = new Persistent();
		$p->GetTableSchema($this->init_tableName);
		if ($p->numRows() > 0) {
			while ($thisRow = $p->getRow()) {
				$fieldName = $thisRow["Field"];
				$this->init_columns[$fieldName] = $fieldName;
				if ($thisRow["Key"] == "PRI") {
					$this->init_primaryKey = $fieldName;
				}
				if (strtolower($fieldName) == "name") {
					$this->init_nameColumn = $fieldName;
				}
			}
			if ($this->init_nameColumn == '') {
				$this->init_nameColumn = $this->init_primaryKey;
			}
		}
	}
	public function __construct($fieldsOrID=array(), $initParams=array()) {
		if (count($initParams) > 0) {
			if (isset($initParams['init_columns'])) {
				$this->setInitColumns($initParams['init_columns']);
			}
			if (isset($initParams['init_primaryKey'])) {
				$this->setPrimaryKey($initParams['init_primaryKey']);
			}
			if (isset($initParams['init_nameColumn'])) {
				$this->setNameColumn($initParams['init_nameColumn']);
			}
		}
		parent::__construct($fieldsOrID);
	}
}


?>
