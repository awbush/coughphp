<?php

/**
 * This is the base class for TableOne.
 * 
 * @see TableOne, CoughObject
 **/
abstract class TableOne_Generated extends CoughObject {
	
	protected static $db = null;
	protected static $dbName = 'test_cough_object';
	protected static $tableName = 'table_one';
	protected static $pkFieldNames = array('table_one_id');
	
	protected $fields = array(
		'table_one_id' => null,
		'name' => "",
	);
	
	protected $fieldDefinitions = array(
		'table_one_id' => array(
			'db_column_name' => 'table_one_id',
			'is_null_allowed' => false,
			'default_value' => null
		),
		'name' => array(
			'db_column_name' => 'name',
			'is_null_allowed' => false,
			'default_value' => ""
		),
	);
	
	protected $objectDefinitions = array();
	
	// Static Definition Methods
	
	public static function getDb() {
		if (is_null(TableOne::$db)) {
			TableOne::$db = CoughDatabaseFactory::getDatabase(TableOne::$dbName);
		}
		return TableOne::$db;
	}
	
	public static function getDbName() {
		return CoughDatabaseFactory::getDatabaseName(TableOne::$dbName);
	}
	
	public static function getTableName() {
		return TableOne::$tableName;
	}
	
	public static function getPkFieldNames() {
		return TableOne::$pkFieldNames;
	}
	
	// Static Construction (factory) Methods
	
	/**
	 * Constructs a new TableOne object from
	 * a single id (for single key PKs) or a hash of [field_name] => [field_value].
	 * 
	 * The key is used to pull data from the database, and, if no data is found,
	 * null is returned. You can use this function with any unique keys or the
	 * primary key as long as a hash is used. If the primary key is a single
	 * field, you may pass its value in directly without using a hash.
	 * 
	 * @param mixed $idOrHash - id or hash of [field_name] => [field_value]
	 * @return mixed - TableOne or null if no record found.
	 **/
	public static function constructByKey($idOrHash, $forPhp5Strict = '') {
		return CoughObject::constructByKey($idOrHash, 'TableOne');
	}
	
	/**
	 * Constructs a new TableOne object from custom SQL.
	 * 
	 * @param string $sql
	 * @return mixed - TableOne or null if exactly one record could not be found.
	 **/
	public static function constructBySql($sql, $forPhp5Strict = '') {
		return CoughObject::constructBySql($sql, 'TableOne');
	}
	
	/**
	 * Constructs a new TableOne object after
	 * checking the fields array to make sure the appropriate subclass is
	 * used.
	 * 
	 * No queries are run against the database.
	 * 
	 * @param array $hash - hash of [field_name] => [field_value] pairs
	 * @return TableOne
	 **/
	public static function constructByFields($hash) {
		return new TableOne($hash);
	}
	
	public static function getLoadSql() {
		$tableName = TableOne::getTableName();
		return '
			SELECT
				`' . $tableName . '`.*
			FROM
				`' . TableOne::getDbName() . '`.`' . $tableName . '`
		';
	}
	
	// Generated attribute accessors (getters and setters)
	
	public function getTableOneId() {
		return $this->getField('table_one_id');
	}
	
	public function setTableOneId($value) {
		$this->setField('table_one_id', $value);
	}
	
	public function getName() {
		return $this->getField('name');
	}
	
	public function setName($value) {
		$this->setField('name', $value);
	}
	
	// Generated one-to-one accessors (loaders, getters, and setters)
	
	// Generated one-to-many collection loaders, getters, setters, adders, and removers
	
}

?>