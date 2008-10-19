<?php

/**
 * This is the base class for CustPc.
 * 
 * @see CustPc, CoughObject
 **/
abstract class CustPc_Generated extends CoughObject {
	
	protected static $db = null;
	protected static $dbName = 'test_cough_object';
	protected static $tableName = 'custPc';
	protected static $pkFieldNames = array('id');
	
	protected $fields = array(
		'id' => null,
		'customerId' => null,
		'popis' => "",
		'macAdresa' => null,
		'networkId' => null,
		'ipAdresa' => null,
		'networkIdVerejna' => null,
		'ipAdresaVerejna' => null,
		'down' => null,
		'up' => null,
	);
	
	protected $fieldDefinitions = array(
		'id' => array(
			'db_column_name' => 'id',
			'is_null_allowed' => false,
			'default_value' => null
		),
		'customerId' => array(
			'db_column_name' => 'customerId',
			'is_null_allowed' => true,
			'default_value' => null
		),
		'popis' => array(
			'db_column_name' => 'popis',
			'is_null_allowed' => false,
			'default_value' => ""
		),
		'macAdresa' => array(
			'db_column_name' => 'macAdresa',
			'is_null_allowed' => true,
			'default_value' => null
		),
		'networkId' => array(
			'db_column_name' => 'networkId',
			'is_null_allowed' => true,
			'default_value' => null
		),
		'ipAdresa' => array(
			'db_column_name' => 'ipAdresa',
			'is_null_allowed' => true,
			'default_value' => null
		),
		'networkIdVerejna' => array(
			'db_column_name' => 'networkIdVerejna',
			'is_null_allowed' => true,
			'default_value' => null
		),
		'ipAdresaVerejna' => array(
			'db_column_name' => 'ipAdresaVerejna',
			'is_null_allowed' => true,
			'default_value' => null
		),
		'down' => array(
			'db_column_name' => 'down',
			'is_null_allowed' => true,
			'default_value' => null
		),
		'up' => array(
			'db_column_name' => 'up',
			'is_null_allowed' => true,
			'default_value' => null
		),
	);
	
	protected $objectDefinitions = array(
		'NetworkId_Object' => array(
			'class_name' => 'Network'
		),
		'NetworkIdVerejna_Object' => array(
			'class_name' => 'Network'
		),
	);
	
	// Static Definition Methods
	
	public static function getDb() {
		if (is_null(CustPc::$db)) {
			CustPc::$db = CoughDatabaseFactory::getDatabase(CustPc::$dbName);
		}
		return CustPc::$db;
	}
	
	public static function getDbName() {
		return CoughDatabaseFactory::getDatabaseName(CustPc::$dbName);
	}
	
	public static function getTableName() {
		return CustPc::$tableName;
	}
	
	public static function getPkFieldNames() {
		return CustPc::$pkFieldNames;
	}
	
	// Static Construction (factory) Methods
	
	/**
	 * Constructs a new CustPc object from
	 * a single id (for single key PKs) or a hash of [field_name] => [field_value].
	 * 
	 * The key is used to pull data from the database, and, if no data is found,
	 * null is returned. You can use this function with any unique keys or the
	 * primary key as long as a hash is used. If the primary key is a single
	 * field, you may pass its value in directly without using a hash.
	 * 
	 * @param mixed $idOrHash - id or hash of [field_name] => [field_value]
	 * @return mixed - CustPc or null if no record found.
	 **/
	public static function constructByKey($idOrHash, $forPhp5Strict = '') {
		return CoughObject::constructByKey($idOrHash, 'CustPc');
	}
	
	/**
	 * Constructs a new CustPc object from custom SQL.
	 * 
	 * @param string $sql
	 * @return mixed - CustPc or null if exactly one record could not be found.
	 **/
	public static function constructBySql($sql, $forPhp5Strict = '') {
		return CoughObject::constructBySql($sql, 'CustPc');
	}
	
	/**
	 * Constructs a new CustPc object after
	 * checking the fields array to make sure the appropriate subclass is
	 * used.
	 * 
	 * No queries are run against the database.
	 * 
	 * @param array $hash - hash of [field_name] => [field_value] pairs
	 * @return CustPc
	 **/
	public static function constructByFields($hash) {
		return new CustPc($hash);
	}
	
	public static function getLoadSql() {
		$tableName = CustPc::getTableName();
		return '
			SELECT
				`' . $tableName . '`.*
			FROM
				`' . CustPc::getDbName() . '`.`' . $tableName . '`
		';
	}
	
	// Generated attribute accessors (getters and setters)
	
	public function getId() {
		return $this->getField('id');
	}
	
	public function setId($value) {
		$this->setField('id', $value);
	}
	
	public function getCustomerId() {
		return $this->getField('customerId');
	}
	
	public function setCustomerId($value) {
		$this->setField('customerId', $value);
	}
	
	public function getPopis() {
		return $this->getField('popis');
	}
	
	public function setPopis($value) {
		$this->setField('popis', $value);
	}
	
	public function getMacAdresa() {
		return $this->getField('macAdresa');
	}
	
	public function setMacAdresa($value) {
		$this->setField('macAdresa', $value);
	}
	
	public function getNetworkId() {
		return $this->getField('networkId');
	}
	
	public function setNetworkId($value) {
		$this->setField('networkId', $value);
	}
	
	public function getIpAdresa() {
		return $this->getField('ipAdresa');
	}
	
	public function setIpAdresa($value) {
		$this->setField('ipAdresa', $value);
	}
	
	public function getNetworkIdVerejna() {
		return $this->getField('networkIdVerejna');
	}
	
	public function setNetworkIdVerejna($value) {
		$this->setField('networkIdVerejna', $value);
	}
	
	public function getIpAdresaVerejna() {
		return $this->getField('ipAdresaVerejna');
	}
	
	public function setIpAdresaVerejna($value) {
		$this->setField('ipAdresaVerejna', $value);
	}
	
	public function getDown() {
		return $this->getField('down');
	}
	
	public function setDown($value) {
		$this->setField('down', $value);
	}
	
	public function getUp() {
		return $this->getField('up');
	}
	
	public function setUp($value) {
		$this->setField('up', $value);
	}
	
	// Generated one-to-one accessors (loaders, getters, and setters)
	
	public function loadNetworkId_Object() {
		$this->setNetworkId_Object(Network::constructByKey($this->getNetworkId()));
	}
	
	public function getNetworkId_Object() {
		if (!isset($this->objects['NetworkId_Object'])) {
			$this->loadNetworkId_Object();
		}
		return $this->objects['NetworkId_Object'];
	}
	
	public function setNetworkId_Object($networkId) {
		$this->objects['NetworkId_Object'] = $networkId;
	}
	
	public function loadNetworkIdVerejna_Object() {
		$this->setNetworkIdVerejna_Object(Network::constructByKey($this->getNetworkIdVerejna()));
	}
	
	public function getNetworkIdVerejna_Object() {
		if (!isset($this->objects['NetworkIdVerejna_Object'])) {
			$this->loadNetworkIdVerejna_Object();
		}
		return $this->objects['NetworkIdVerejna_Object'];
	}
	
	public function setNetworkIdVerejna_Object($networkIdVerejna) {
		$this->objects['NetworkIdVerejna_Object'] = $networkIdVerejna;
	}
	
	// Generated one-to-many collection loaders, getters, setters, adders, and removers
	
}

?>