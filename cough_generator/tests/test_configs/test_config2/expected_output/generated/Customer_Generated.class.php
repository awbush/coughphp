<?php

/**
 * This is the base class for Customer.
 * 
 * @see Customer, CoughObject
 **/
abstract class Customer_Generated extends CoughObject {
	
	protected static $db = null;
	protected static $dbName = 'test_cough_object';
	protected static $tableName = 'customer';
	protected static $pkFieldNames = array('id');
	
	protected $fields = array(
		'id' => "",
	);
	
	protected $fieldDefinitions = array(
		'id' => array(
			'db_column_name' => 'id',
			'is_null_allowed' => false,
			'default_value' => ""
		),
	);
	
	protected $objectDefinitions = array();
	
	// Static Definition Methods
	
	public static function getDb() {
		if (is_null(Customer::$db)) {
			Customer::$db = CoughDatabaseFactory::getDatabase(Customer::$dbName);
		}
		return Customer::$db;
	}
	
	public static function getDbName() {
		return CoughDatabaseFactory::getDatabaseName(Customer::$dbName);
	}
	
	public static function getTableName() {
		return Customer::$tableName;
	}
	
	public static function getPkFieldNames() {
		return Customer::$pkFieldNames;
	}
	
	// Static Construction (factory) Methods
	
	/**
	 * Constructs a new Customer object from
	 * a single id (for single key PKs) or a hash of [field_name] => [field_value].
	 * 
	 * The key is used to pull data from the database, and, if no data is found,
	 * null is returned. You can use this function with any unique keys or the
	 * primary key as long as a hash is used. If the primary key is a single
	 * field, you may pass its value in directly without using a hash.
	 * 
	 * @param mixed $idOrHash - id or hash of [field_name] => [field_value]
	 * @return mixed - Customer or null if no record found.
	 **/
	public static function constructByKey($idOrHash, $forPhp5Strict = '') {
		return CoughObject::constructByKey($idOrHash, 'Customer');
	}
	
	/**
	 * Constructs a new Customer object from custom SQL.
	 * 
	 * @param string $sql
	 * @return mixed - Customer or null if exactly one record could not be found.
	 **/
	public static function constructBySql($sql, $forPhp5Strict = '') {
		return CoughObject::constructBySql($sql, 'Customer');
	}
	
	/**
	 * Constructs a new Customer object after
	 * checking the fields array to make sure the appropriate subclass is
	 * used.
	 * 
	 * No queries are run against the database.
	 * 
	 * @param array $hash - hash of [field_name] => [field_value] pairs
	 * @return Customer
	 **/
	public static function constructByFields($hash) {
		return new Customer($hash);
	}
	
	public function notifyChildrenOfKeyChange(array $key) {
		foreach ($this->getProductOrder_Collection() as $productOrder) {
			$productOrder->setCustomerId($key['id']);
		}
	}
	
	public static function getLoadSql() {
		$tableName = Customer::getTableName();
		return '
			SELECT
				`' . $tableName . '`.*
			FROM
				`' . Customer::getDbName() . '`.`' . $tableName . '`
		';
	}
	
	// Generated attribute accessors (getters and setters)
	
	public function getId() {
		return $this->getField('id');
	}
	
	public function setId($value) {
		$this->setField('id', $value);
	}
	
	// Generated one-to-one accessors (loaders, getters, and setters)
	
	// Generated one-to-many collection loaders, getters, setters, adders, and removers
	
	public function loadProductOrder_Collection() {
		
		// Always create the collection
		$collection = new ProductOrder_Collection();
		$this->setProductOrder_Collection($collection);
		
		// But only populate it if we have key ID
		if ($this->hasKeyId()) {
			$db = ProductOrder::getDb();
			$tableName = ProductOrder::getTableName();
			$sql = '
				SELECT
					`' . $tableName . '`.*
				FROM
					`' . ProductOrder::getDbName() . '`.`' . $tableName . '`
				WHERE
					`' . $tableName . '`.`customer_id` = ' . $db->quote($this->getId()) . '
			';

			// Construct and populate the collection
			$collection->loadBySql($sql);
			foreach ($collection as $element) {
				$element->setCustomer_Object($this);
			}
		}
	}
	
	public function getProductOrder_Collection() {
		if (!isset($this->collections['ProductOrder_Collection'])) {
			$this->loadProductOrder_Collection();
		}
		return $this->collections['ProductOrder_Collection'];
	}
	
	public function setProductOrder_Collection($productOrderCollection) {
		$this->collections['ProductOrder_Collection'] = $productOrderCollection;
	}
	
	public function addProductOrder(ProductOrder $object) {
		$object->setCustomerId($this->getId());
		$object->setCustomer_Object($this);
		$this->getProductOrder_Collection()->add($object);
		return $object;
	}
	
	public function removeProductOrder($objectOrId) {
		$removedObject = $this->getProductOrder_Collection()->remove($objectOrId);
		if (is_object($removedObject)) {
			$removedObject->setCustomerId("");
			$removedObject->setCustomer_Object(null);
		}
		return $removedObject;
	}
	
}

?>