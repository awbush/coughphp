<?php

/**
 * Take a tableName and database link and provide access to the tables
 * properties, such as its columns.
 * 
 * @package Schema
 * @author Anthony Bush
 * @copyright Anthony Bush (http://anthonybush.com/), 2006-08-26
 **/
class MysqlTable extends SchemaTable implements DriverTable {
	
	protected $dbLink = null;
	
	public function __construct($tableName, $dbLink = null, $database = null) {
		$this->setTableName($tableName);
		$this->setDatabase($database);
		$this->dbLink = $dbLink;
	}
	
	public function loadColumns() {
		$sql = 'SHOW COLUMNS FROM `' . $this->tableName . '`';
		$result = $this->query($sql);
		$this->columns = array();
		while ($row = mysql_fetch_assoc($result)) {
			$column = new SchemaColumn();
			$column->setTable($this);
			$column->setColumnName($row['Field']);
			$column->setIsNullAllowed((strtolower($row['Null']) == 'yes'));
			$column->setDefaultValue($row['Default']); // value from MySQL will be PHP null, false, true, or a string
			$column->setIsPrimaryKey(($row['Key'] == 'PRI'));
			
			// Set type and size of column
			$typePieces = preg_split('/[\(\)]/',$row['Type']);
			$column->setType($typePieces[0]);
			if (count($typePieces) > 1) {
				$column->setSize($typePieces[1]);
			}
			
			$this->columns[$column->getColumnName()] = $column;
		}
	}
	
	protected function query($sql) {
		if (is_null($this->dbLink)) {
			$result = mysql_query($sql);
		} else {
			$result = mysql_query($sql, $this->dbLink);
		}
		if ( ! $result) {
			$this->generateError('Invalid query');
		} else {
			return $result;
		}
	}
	
	protected function generateError($msg) {
		if (is_null($this->dbLink)) {
			throw new Exception($msg . ': ' . mysql_error());
		} else {
			throw new Exception($msg . ': ' . mysql_error($this->dbLink));
		}
	}
	
}

?>