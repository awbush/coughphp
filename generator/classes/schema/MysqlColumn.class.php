<?php

class MysqlColumn extends AbstractColumn {
	protected $columnDef = null;
	
	public function __construct($columnDef, $table = null) {
		$this->table = $table;
		$this->columnDef = $columnDef;
		if (is_array($columnDef) && isset($columnDef['Field'])) {
			$this->parseShowColumnDef();
		}
	}
	
	/**
	 * Parses a column definition of array type from a SHOW COLUMNS FROM
	 * tableName query. That is, the data might look like:
	 * 
	 * Array
	 * (
	 *     [Field] => subject_id
	 *     [Type] => int(10) unsigned
	 *     [Null] => NO
	 *     [Key] => PRI
	 *     [Default] => 
	 *     [Extra] => auto_increment
	 * )
	 * 
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function parseShowColumnDef() {
		
		// Column Name
		$this->columnName = $this->columnDef['Field'];
		
		// NULL allowed?
		if (strtolower($this->columnDef['Null']) == 'yes') {
			$this->isNullAllowed = true;
		} else {
			$this->isNullAllowed = false;
		}
		
		// Set default value (value from MySQL will be PHP null, false, true, or a string)
		$this->defaultValue = $this->columnDef['Default'];
		
		// Set type and size of column
		$typePieces = preg_split('/[\(\)]/',$this->columnDef['Type']);
		$this->type = $typePieces[0];
		if (count($typePieces) > 1) {
			$this->size = $typePieces[1];
		}
		
		// Primary Key?
		if ($this->columnDef['Key'] == 'PRI') {
			$this->isPrimaryKey = true;
		} else {
			$this->isPrimaryKey = false;
		}
		
	}
	
}

?>