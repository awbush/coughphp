<?php

/**
 * database abstraction layer abstraction layer
 *
 * @package default
 * @author Lewis Zhang
 **/
abstract class CoughAbstractDatabaseAdapter
{
	/**
	 * database abstraction layer object
	 * ex: PDO, AdoDB, MDB, Creole, etc
	 *
	 **/
	protected $db = null;
	
	/**
	 * sets the new database object
	 *
	 * @return void
	 * @author Lewis Zhang
	 **/
	protected function __construct($db)
	{
		$this->db = $db;
	}
	
	abstract public function query($sql);
	
	abstract public function execute($sql);
	
	abstract public function getLastInsertId();
	
	/**
	 * returns a quoted version of the provided string using the underlying database's quoting mechanism
	 * call quote instead for normal usage
	 *
	 * @return string
	 * @author Lewis Zhang
	 **/
	abstract public function dbQuote($string);
	
	/**
	 * returns the escaped version of the provided value 
	 *
	 * @return string
	 * @author Lewis Zhang
	 **/
	abstract public function escape($string);
	
	/**
	 * returns a database object
	 *
	 * @return object - returns a concrete child of CoughAbstractDatabaseAdapter
	 * @author Lewis Zhang
	 **/
	abstract public static function retrieveByDbConfig($dbConfig);
	
	/**
	 * selects the specified database for this connection
	 * TODO: is this the right thing to do? I'm assuming this is MySQL specific as well
	 *
	 * @return CoughAbstractDatabaseResultAdapter
	 * @author Lewis Zhang
	 **/
	public function selectDb($databaseName)
	{
		return $this->query("use `$databaseName`");
	}
	
	/**
	 * returns the first value of the result of a select query
	 *
	 * @return mixed
	 * @author Lewis Zhang
	 **/
	public function result($sql)
	{
		$result = $this->query($sql)->getRow();
		$numElements = count($result);
		
		if ($result === false) {
			return null;
		}
		else if ($numElements == 1) {
			return array_pop($result);
		}
		else if ($numElements > 1) {
			return $result;
		}
	}
	
	/**
	 * returns a quoted version of the provided value
	 *
	 * @return string
	 * @author Lewis Zhang
	 **/
	public function quote($string)
	{
		if ($string === null) {
			return 'NULL';
		}
		else if ($string === true) {
			return 1;
		}
		else if ($string === false) {
			return 0;
		}
		else if ($string instanceof SqlFunction) {
			return $string->getString();
		}
		else {
			return $this->dbQuote($string);
		}
	}
	
	/**
	 * returns a backticked version of the supplied string
	 *
	 * @return string
	 * @author Lewis Zhang
	 **/
	public function backtick($string)
	{
		if ($string instanceof SqlFunction) {
			return $string->getString();
		}
		else {
			$stringParts = explode('.', $string);
			if (count($stringParts) == 1) {
				return "`$string`";
			}
			else {
				$backtickedStringParts = array();
				foreach ($stringParts as $stringPart) {
					$backtickedStringParts[] = "`$stringPart`";
				}
				return implode('.', $backtickedStringParts);
			}
		}
	}
	
	/**
	 * returns the result of a SELECT query from the supplied arguments
	 *
	 * @return CoughAbstractDatabaseResultAdapter
	 * @author Lewis Zhang
	 **/
	public function select($tableName, $fieldNames, $where)
	{
		return $this->query($this->buildSelectSql($tableName, $fieldNames, $where));
	}
	
	/**
	 * builds a simple SELECT from the supplied parameters and returns the sql
	 *
	 * @return string
	 * @author Lewis Zhang
	 **/
	public function buildSelectSql($tableName, $fieldNames, $where)
	{
		$tableName = $this->backtick($tableName);
		
		$fieldNames = array_map(array($this, 'backtick'), $fieldNames);
		$fieldNamesSql = implode(', ', $fieldNames);
		
		$whereSql = $this->buildWhereSql($where);
		
		$sql = "
			SELECT
				$fieldNamesSql
			FROM
				$tableName
			WHERE
				$whereSql
		";
		
		return $sql;
	}
	
	/**
	 * executes an insert and returns the last insert id 
	 *
	 * @return mixed
	 * @author Lewis Zhang
	 **/
	public function insert($table, $fields)
	{
		$numAffectedRows = $this->execute($this->buildInsertSql($table, $fields));
		if ($numAffectedRows == 1) {
			return $this->getLastInsertId();
		}
		else {
			return false;
		}
	}
	
	/**
	 * builds and returns the sql that would insert a row with the provided fields into the specified table
	 *
	 * @return string
	 * @author Lewis Zhang
	 **/
	public function buildInsertSql($tableName, $fields)
	{
		$tableName = $this->backtick($tableName);
		
		$fieldNames = array();
		$fieldValues = array();
		foreach ($fields as $fieldName => $fieldValue) {
			$fieldNames[] = $this->backtick($fieldName);
			$fieldValues[] = $this->quote($fieldValue);
		}
		$fieldNamesSql = implode(', ', $fieldNames);
		$fieldValuesSql = implode(', ', $fieldValues);
		
		$sql = "
			INSERT INTO
				$tableName
			(
				$fieldNamesSql
			)
			VALUES
			(
				$fieldValuesSql
			)
		";
				
		return $sql;
	}
	
	/**
	 * executes an update and returns the number of affected rows
	 *
	 * @return int
	 * @author Lewis Zhang
	 **/
	public function update($tableName, $fields, $where)
	{
		return $this->execute($this->buildUpdateSql($tableName, $fields, $where));
	}
	
	/**
	 * builds an UPDATE query from the supplied arguments
	 *
	 * @return string
	 * @author Lewis Zhang
	 **/
	public function buildUpdateSql($tableName, $fields, $where)
	{
		$tableName = $this->backtick($tableName);
		$fieldAssignmentsSql = $this->buildSetSql($fields);
		$whereSql = $this->buildWhereSql($where);
		
		$sql = "
			UPDATE
				$tableName
			SET
				$fieldAssignmentsSql
			WHERE
				$whereSql
		";
		
		return $sql;
	}
	
	/**
	 * builds a comma separated list of field to value assignments
	 *
	 * @return string
	 * @author Lewis Zhang
	 **/
	public function buildSetSql($fields)
	{
		$fieldAssignments = array();
		foreach ($fields as $fieldName => $fieldValue) {
			$fieldAssignments[] = $this->backtick($fieldName) . ' = ' . $this->quote($fieldValue); 
		}
		$fieldAssignmentsSql = implode(', ', $fieldAssignments);
		
		return $fieldAssignmentsSql;
	}
	
	/**
	 * executes a DELETE query from the supplied arguments and returns the number of affected rows
	 *
	 * @return int
	 * @author Lewis Zhang
	 **/
	public function delete($tableName, $where)
	{
		return $this->execute($this->buildDeleteSql($tableName, $where));
	}
	
	/**
	 * builds a DELETE query and returns it
	 *
	 * @return string
	 * @author Lewis Zhang
	 **/
	public function buildDeleteSql($tableName, $where)
	{
		$tableName = $this->backtick($tableName);
		$whereSql = $this->buildWhereSql($where);
		
		$sql = "
			DELETE
				$tableName
			WHERE
				$whereSql
		";
		
		return $sql;
	}
	
	/**
	 * executes an INSERT query or performs an UPDATE, depending on whether the where condition applies to
	 * any existing rows
	 *
	 * @return void
	 * @author Lewis Zhang
	 **/
	public function insertOrUpdate($tableName, $fields, $where)
	{
		$numRows = $this->result($this->buildSelectSql($tableName, array(new SqlFunction('COUNT(*)')), $where));
		if ($numRows > 0) {
			$this->update($tableName, $fields, $where);
		}
		else {
			$this->insert($tableName, $fields, $where);
		}
	}
	
	/**
	 * INSERTs or UPDATEs depending on whether a row with duplicate keys is found
	 * TODO: this may not have an analogue in non-MySQL databases... maybe this should be removed since it might
	 * break future compatibility with other databases
	 *
	 * @return int - the number of affected rows
	 * @author Lewis Zhang
	 **/
	public function insertOnDuplicateKeyUpdate($tableName, $fields)
	{
		return $this->execute($this->buildInsertOnDuplicateKeyUpdateSql($tableName, $fields));
	}
	
	/**
	 * builds and returns the insert... on duplicate key update sql
	 * WARNING: this is untested and should be... TODO: test me
	 *
	 * @return string
	 * @author Lewis Zhang
	 **/
	public function buildInsertOnDuplicateKeyUpdateSql($tableName, $fields)
	{
		$fieldsSetSql = $this->buildSetSql($fields);
		$sql  = $this->buildInsertSql($tableName, $fields);
		$sql .= "
			ON DUPLICATE KEY UPDATE
				$fieldsSetSql
		";
		
		return $sql;
	}
	
	/**
	 * builds a WHERE clause from the supplied array
	 *
	 * @return string
	 * @author Lewis Zhang
	 **/
	public function buildWhereSql($where)
	{
		if (empty($where)) {
			return '';
		}
		else {
			$clauses = array();
			foreach ($where as $fieldName => $fieldValue) {
				$clauses[] = $this->backtick($fieldName) . $this->getEqualityOperatorFromValue($fieldValue) . $this->quote($fieldValue);
			}
			$clauseSql = implode(' AND ', $clauses);
			
			return $clauseSql;
		}
	}
	
	/**
	 * returns the proper equality operator from the value being tested
	 *
	 * @return string
	 * @author Lewis Zhang
	 **/
	public function getEqualityOperatorFromValue($fieldValue)
	{
		if ($fieldValue === null) {
			return ' IS ';
		}
		else {
			return ' = ';
		}
	}
	
} // END class CoughAbstractDatabaseAdapter

?>