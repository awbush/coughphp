<?php

/**
 * This is the base class for Network.
 * 
 * @see Network, CoughObject
 **/
abstract class Network_Generated extends CoughObject {
	
	protected static $db = null;
	protected static $dbName = 'test_cough_object';
	protected static $tableName = 'network';
	protected static $pkFieldNames = array('id');
	
	protected $fields = array(
		'id' => null,
		'interfaceId' => null,
		'ipAdresa' => "",
		'maska' => "",
		'ipRouter' => null,
		'verejna' => 0,
		'isp' => "sumnet",
	);
	
	protected $fieldDefinitions = array(
		'id' => array(
			'db_column_name' => 'id',
			'is_null_allowed' => false,
			'default_value' => null
		),
		'interfaceId' => array(
			'db_column_name' => 'interfaceId',
			'is_null_allowed' => true,
			'default_value' => null
		),
		'ipAdresa' => array(
			'db_column_name' => 'ipAdresa',
			'is_null_allowed' => false,
			'default_value' => ""
		),
		'maska' => array(
			'db_column_name' => 'maska',
			'is_null_allowed' => false,
			'default_value' => ""
		),
		'ipRouter' => array(
			'db_column_name' => 'ipRouter',
			'is_null_allowed' => true,
			'default_value' => null
		),
		'verejna' => array(
			'db_column_name' => 'verejna',
			'is_null_allowed' => false,
			'default_value' => 0
		),
		'isp' => array(
			'db_column_name' => 'isp',
			'is_null_allowed' => false,
			'default_value' => "sumnet"
		),
	);
	
	protected $objectDefinitions = array();
	
	// Static Definition Methods
	
	public static function getDb() {
		if (is_null(Network::$db)) {
			Network::$db = CoughDatabaseFactory::getDatabase(Network::$dbName);
		}
		return Network::$db;
	}
	
	public static function getDbName() {
		return CoughDatabaseFactory::getDatabaseName(Network::$dbName);
	}
	
	public static function getTableName() {
		return Network::$tableName;
	}
	
	public static function getPkFieldNames() {
		return Network::$pkFieldNames;
	}
	
	// Static Construction (factory) Methods
	
	/**
	 * Constructs a new Network object from
	 * a single id (for single key PKs) or a hash of [field_name] => [field_value].
	 * 
	 * The key is used to pull data from the database, and, if no data is found,
	 * null is returned. You can use this function with any unique keys or the
	 * primary key as long as a hash is used. If the primary key is a single
	 * field, you may pass its value in directly without using a hash.
	 * 
	 * @param mixed $idOrHash - id or hash of [field_name] => [field_value]
	 * @return mixed - Network or null if no record found.
	 **/
	public static function constructByKey($idOrHash, $forPhp5Strict = '') {
		return CoughObject::constructByKey($idOrHash, 'Network');
	}
	
	/**
	 * Constructs a new Network object from custom SQL.
	 * 
	 * @param string $sql
	 * @return mixed - Network or null if exactly one record could not be found.
	 **/
	public static function constructBySql($sql, $forPhp5Strict = '') {
		return CoughObject::constructBySql($sql, 'Network');
	}
	
	/**
	 * Constructs a new Network object after
	 * checking the fields array to make sure the appropriate subclass is
	 * used.
	 * 
	 * No queries are run against the database.
	 * 
	 * @param array $hash - hash of [field_name] => [field_value] pairs
	 * @return Network
	 **/
	public static function constructByFields($hash) {
		return new Network($hash);
	}
	
	public function notifyChildrenOfKeyChange(array $key) {
		foreach ($this->getCustPc_Collection_ByNetworkId() as $custPc) {
			$custPc->setNetworkId($key['id']);
		}
		foreach ($this->getCustPc_Collection_ByNetworkIdVerejna() as $custPc) {
			$custPc->setNetworkIdVerejna($key['id']);
		}
	}
	
	public static function getLoadSql() {
		$tableName = Network::getTableName();
		return '
			SELECT
				`' . $tableName . '`.*
			FROM
				`' . Network::getDbName() . '`.`' . $tableName . '`
		';
	}
	
	// Generated attribute accessors (getters and setters)
	
	public function getId() {
		return $this->getField('id');
	}
	
	public function setId($value) {
		$this->setField('id', $value);
	}
	
	public function getInterfaceId() {
		return $this->getField('interfaceId');
	}
	
	public function setInterfaceId($value) {
		$this->setField('interfaceId', $value);
	}
	
	public function getIpAdresa() {
		return $this->getField('ipAdresa');
	}
	
	public function setIpAdresa($value) {
		$this->setField('ipAdresa', $value);
	}
	
	public function getMaska() {
		return $this->getField('maska');
	}
	
	public function setMaska($value) {
		$this->setField('maska', $value);
	}
	
	public function getIpRouter() {
		return $this->getField('ipRouter');
	}
	
	public function setIpRouter($value) {
		$this->setField('ipRouter', $value);
	}
	
	public function getVerejna() {
		return $this->getField('verejna');
	}
	
	public function setVerejna($value) {
		$this->setField('verejna', $value);
	}
	
	public function getIsp() {
		return $this->getField('isp');
	}
	
	public function setIsp($value) {
		$this->setField('isp', $value);
	}
	
	// Generated one-to-one accessors (loaders, getters, and setters)
	
	// Generated one-to-many collection loaders, getters, setters, adders, and removers
	
	public function loadCustPc_Collection_ByNetworkId() {
		
		// Always create the collection
		$collection = new CustPc_Collection();
		$this->setCustPc_Collection_ByNetworkId($collection);
		
		// But only populate it if we have key ID
		if ($this->hasKeyId()) {
			$db = CustPc::getDb();
			$tableName = CustPc::getTableName();
			$sql = '
				SELECT
					`' . $tableName . '`.*
				FROM
					`' . CustPc::getDbName() . '`.`' . $tableName . '`
				WHERE
					`' . $tableName . '`.`networkId` = ' . $db->quote($this->getId()) . '
			';

			// Construct and populate the collection
			$collection->loadBySql($sql);
			foreach ($collection as $element) {
				$element->setNetworkId_Object($this);
			}
		}
	}
	
	public function getCustPc_Collection_ByNetworkId() {
		if (!isset($this->collections['CustPc_Collection_ByNetworkId'])) {
			$this->loadCustPc_Collection_ByNetworkId();
		}
		return $this->collections['CustPc_Collection_ByNetworkId'];
	}
	
	public function setCustPc_Collection_ByNetworkId($custPcCollection) {
		$this->collections['CustPc_Collection_ByNetworkId'] = $custPcCollection;
	}
	
	public function loadCustPc_Collection_ByNetworkIdVerejna() {
		
		// Always create the collection
		$collection = new CustPc_Collection();
		$this->setCustPc_Collection_ByNetworkIdVerejna($collection);
		
		// But only populate it if we have key ID
		if ($this->hasKeyId()) {
			$db = CustPc::getDb();
			$tableName = CustPc::getTableName();
			$sql = '
				SELECT
					`' . $tableName . '`.*
				FROM
					`' . CustPc::getDbName() . '`.`' . $tableName . '`
				WHERE
					`' . $tableName . '`.`networkIdVerejna` = ' . $db->quote($this->getId()) . '
			';

			// Construct and populate the collection
			$collection->loadBySql($sql);
			foreach ($collection as $element) {
				$element->setNetworkIdVerejna_Object($this);
			}
		}
	}
	
	public function getCustPc_Collection_ByNetworkIdVerejna() {
		if (!isset($this->collections['CustPc_Collection_ByNetworkIdVerejna'])) {
			$this->loadCustPc_Collection_ByNetworkIdVerejna();
		}
		return $this->collections['CustPc_Collection_ByNetworkIdVerejna'];
	}
	
	public function setCustPc_Collection_ByNetworkIdVerejna($custPcCollection) {
		$this->collections['CustPc_Collection_ByNetworkIdVerejna'] = $custPcCollection;
	}
	
}

?>