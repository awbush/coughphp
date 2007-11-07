<?php

/**
 * Implements the interface for Table Driver, which:
 * 
 * Take a tableName and database link and provide access to the tables
 * properties, such as its columns.
 * 
 * @package schema_generator
 * @author Anthony Bush
 **/
class MysqlTable extends SchemaTable implements DriverTable {
	
	protected $dbLink = null;
	
	public function __construct($tableName, $dbLink = null, $database = null) {
		$this->setTableName($tableName);
		$this->setDatabase($database);
		$this->dbLink = $dbLink;
	}
	
	public function loadColumns() {
		$sql = 'SHOW COLUMNS FROM `' . $this->getTableName() . '`';
		$result = $this->query($sql);
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
			
			$this->addColumn($column);
		}
		
		$sql = 'SHOW CREATE TABLE `' . $this->getTableName() . '`';
		$result = $this->query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			if (isset($row['Create Table'])) {
				$this->parseCreateStatement($row['Create Table']);
			}
		}
		
	}
	
	protected function parseCreateStatement($createSql) {
		$this->parseForeignKeyReferences($createSql);
	}
	
	public function parseForeignKeyReferences($createSql) {
		// This regex has the following restrictions: A column name can not contain a ")" or a " ".
		$pattern = '|FOREIGN KEY[^(]*\(([^)]+)\)[ ]*REFERENCES[ ]*([^ ]+)[ ]+\(([^)]+)\)|';

		$matches = array();
		$count = preg_match_all($pattern, $createSql, $matches, PREG_SET_ORDER);

		foreach ($matches as $match) {
			$fKeyString   = $match[1];
			$refTable     = $this->trimBackticks($match[2]);
			$refKeyString = $match[3];

			$fKey = array();
			foreach (explode(',', $fKeyString) as $quotedKey) {
				$fKey[] = $this->trimBackticks($quotedKey); 
			}

			$refKey = array();
			foreach (explode(',', $refKeyString) as $quotedKey) {
				$refKey[] = $this->trimBackticks($quotedKey);
			}
			
			$dbName = $this->getDatabase()->getDatabaseName();
			$schemaFk = new SchemaForeignKey();
			$schemaFk->setLocalDatabaseName($dbName);
			$schemaFk->setLocalTableName($this->getTableName());
			$schemaFk->setLocalKeyName($fKey);
			$schemaFk->setRefDatabaseName($dbName);
			$schemaFk->setRefTableName($refTable);
			$schemaFk->setRefKeyName($refKey);
			$this->addForeignKey($schemaFk);

			// echo 'Table ' . $this->getTableName() . ' has FK ' . implode(',', $fKey) . ' which points to table ' . $refTable . ' (' . implode(',', $refKey) . ')' . "\n";
		}
		
	}
	
	/**
	 * Trim whitespace, and THEN backticks (leaving any whitespice within the backticks)
	 *
	 * @return string
	 **/
	protected function trimBackticks($str) {
		return trim(trim($str), '`');
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
