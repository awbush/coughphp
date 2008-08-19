<?php

/**
 * This is the base class for ProductOrder.
 * 
 * @see ProductOrder, CoughObject
 **/
abstract class ProductOrder_Generated extends CoughObject {
	
	protected static $db = null;
	protected static $dbName = 'test_cough_object';
	protected static $tableName = 'product_order';
	protected static $pkFieldNames = array('no');
	
	protected $fields = array(
		'no' => null,
		'product_category' => "",
		'product_id' => "",
		'customer_id' => "",
	);
	
	protected $fieldDefinitions = array(
		'no' => array(
			'db_column_name' => 'no',
			'is_null_allowed' => false,
			'default_value' => null
		),
		'product_category' => array(
			'db_column_name' => 'product_category',
			'is_null_allowed' => false,
			'default_value' => ""
		),
		'product_id' => array(
			'db_column_name' => 'product_id',
			'is_null_allowed' => false,
			'default_value' => ""
		),
		'customer_id' => array(
			'db_column_name' => 'customer_id',
			'is_null_allowed' => false,
			'default_value' => ""
		),
	);
	
	protected $objectDefinitions = array(
		'Product_Object' => array(
			'class_name' => 'Product'
		),
		'Customer_Object' => array(
			'class_name' => 'Customer'
		),
	);
	
	// Static Definition Methods
	
	public static function getDb() {
		if (is_null(ProductOrder::$db)) {
			ProductOrder::$db = CoughDatabaseFactory::getDatabase(ProductOrder::$dbName);
		}
		return ProductOrder::$db;
	}
	
	public static function getDbName() {
		return CoughDatabaseFactory::getDatabaseName(ProductOrder::$dbName);
	}
	
	public static function getTableName() {
		return ProductOrder::$tableName;
	}
	
	public static function getPkFieldNames() {
		return ProductOrder::$pkFieldNames;
	}
	
	// Static Construction (factory) Methods
	
	/**
	 * Constructs a new ProductOrder object from
	 * a single id (for single key PKs) or a hash of [field_name] => [field_value].
	 * 
	 * The key is used to pull data from the database, and, if no data is found,
	 * null is returned. You can use this function with any unique keys or the
	 * primary key as long as a hash is used. If the primary key is a single
	 * field, you may pass its value in directly without using a hash.
	 * 
	 * @param mixed $idOrHash - id or hash of [field_name] => [field_value]
	 * @return mixed - ProductOrder or null if no record found.
	 **/
	public static function constructByKey($idOrHash, $forPhp5Strict = '') {
		return CoughObject::constructByKey($idOrHash, 'ProductOrder');
	}
	
	/**
	 * Constructs a new ProductOrder object from custom SQL.
	 * 
	 * @param string $sql
	 * @return mixed - ProductOrder or null if exactly one record could not be found.
	 **/
	public static function constructBySql($sql, $forPhp5Strict = '') {
		return CoughObject::constructBySql($sql, 'ProductOrder');
	}
	
	/**
	 * Constructs a new ProductOrder object after
	 * checking the fields array to make sure the appropriate subclass is
	 * used.
	 * 
	 * No queries are run against the database.
	 * 
	 * @param array $hash - hash of [field_name] => [field_value] pairs
	 * @return ProductOrder
	 **/
	public static function constructByFields($hash) {
		return new ProductOrder($hash);
	}
	
	public static function getLoadSql() {
		$tableName = ProductOrder::getTableName();
		return '
			SELECT
				`' . $tableName . '`.*
			FROM
				`' . ProductOrder::getDbName() . '`.`' . $tableName . '`
		';
	}
	
	// Generated attribute accessors (getters and setters)
	
	public function getNo() {
		return $this->getField('no');
	}
	
	public function setNo($value) {
		$this->setField('no', $value);
	}
	
	public function getProductCategory() {
		return $this->getField('product_category');
	}
	
	public function setProductCategory($value) {
		$this->setField('product_category', $value);
	}
	
	public function getProductId() {
		return $this->getField('product_id');
	}
	
	public function setProductId($value) {
		$this->setField('product_id', $value);
	}
	
	public function getCustomerId() {
		return $this->getField('customer_id');
	}
	
	public function setCustomerId($value) {
		$this->setField('customer_id', $value);
	}
	
	// Generated one-to-one accessors (loaders, getters, and setters)
	
	public function loadProduct_Object() {
		$tableName = Product::getTableName();
		$product = Product::constructByKey(array(
			'`' . $tableName . '`.`category`' => $this->getProductCategory(),
			'`' . $tableName . '`.`id`' => $this->getProductId(),
		));
		$this->setProduct_Object($product);
	}
	
	public function getProduct_Object() {
		if (!isset($this->objects['Product_Object'])) {
			$this->loadProduct_Object();
		}
		return $this->objects['Product_Object'];
	}
	
	public function setProduct_Object($product) {
		$this->objects['Product_Object'] = $product;
	}
	
	public function loadCustomer_Object() {
		$this->setCustomer_Object(Customer::constructByKey($this->getCustomerId()));
	}
	
	public function getCustomer_Object() {
		if (!isset($this->objects['Customer_Object'])) {
			$this->loadCustomer_Object();
		}
		return $this->objects['Customer_Object'];
	}
	
	public function setCustomer_Object($customer) {
		$this->objects['Customer_Object'] = $customer;
	}
	
	// Generated one-to-many collection loaders, getters, setters, adders, and removers
	
}

?>