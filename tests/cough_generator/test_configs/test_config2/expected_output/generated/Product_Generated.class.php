<?php

/**
 * This is the base class for Product.
 * 
 * @see Product, CoughObject
 **/
abstract class Product_Generated extends CoughObject {
	
	protected static $db = null;
	protected static $dbName = 'test_cough_object';
	protected static $tableName = 'product';
	protected static $pkFieldNames = array('category','id');
	
	protected $fields = array(
		'category' => "",
		'id' => "",
		'price' => null,
	);
	
	protected $fieldDefinitions = array(
		'category' => array(
			'db_column_name' => 'category',
			'is_null_allowed' => false,
			'default_value' => ""
		),
		'id' => array(
			'db_column_name' => 'id',
			'is_null_allowed' => false,
			'default_value' => ""
		),
		'price' => array(
			'db_column_name' => 'price',
			'is_null_allowed' => true,
			'default_value' => null
		),
	);
	
	protected $objectDefinitions = array();
	
	// Static Definition Methods
	
	public static function getDb() {
		if (is_null(Product::$db)) {
			Product::$db = CoughDatabaseFactory::getDatabase(Product::$dbName);
		}
		return Product::$db;
	}
	
	public static function getDbName() {
		return CoughDatabaseFactory::getDatabaseName(Product::$dbName);
	}
	
	public static function getTableName() {
		return Product::$tableName;
	}
	
	public static function getPkFieldNames() {
		return Product::$pkFieldNames;
	}
	
	// Static Construction (factory) Methods
	
	/**
	 * Constructs a new Product object from
	 * a single id (for single key PKs) or a hash of [field_name] => [field_value].
	 * 
	 * The key is used to pull data from the database, and, if no data is found,
	 * null is returned. You can use this function with any unique keys or the
	 * primary key as long as a hash is used. If the primary key is a single
	 * field, you may pass its value in directly without using a hash.
	 * 
	 * @param mixed $idOrHash - id or hash of [field_name] => [field_value]
	 * @return mixed - Product or null if no record found.
	 **/
	public static function constructByKey($idOrHash, $forPhp5Strict = '') {
		return CoughObject::constructByKey($idOrHash, 'Product');
	}
	
	/**
	 * Constructs a new Product object from custom SQL.
	 * 
	 * @param string $sql
	 * @return mixed - Product or null if exactly one record could not be found.
	 **/
	public static function constructBySql($sql, $forPhp5Strict = '') {
		return CoughObject::constructBySql($sql, 'Product');
	}
	
	/**
	 * Constructs a new Product object after
	 * checking the fields array to make sure the appropriate subclass is
	 * used.
	 * 
	 * No queries are run against the database.
	 * 
	 * @param array $hash - hash of [field_name] => [field_value] pairs
	 * @return Product
	 **/
	public static function constructByFields($hash) {
		return new Product($hash);
	}
	
	public function notifyChildrenOfKeyChange(array $key) {
		foreach ($this->getProductOrder_Collection() as $productOrder) {
			$productOrder->setProductCategory($key['category']);
			$productOrder->setProductId($key['id']);
		}
	}
	
	public static function getLoadSql() {
		$tableName = Product::getTableName();
		return '
			SELECT
				`' . $tableName . '`.*
			FROM
				`' . Product::getDbName() . '`.`' . $tableName . '`
		';
	}
	
	// Generated attribute accessors (getters and setters)
	
	public function getCategory() {
		return $this->getField('category');
	}
	
	public function setCategory($value) {
		$this->setField('category', $value);
	}
	
	public function getId() {
		return $this->getField('id');
	}
	
	public function setId($value) {
		$this->setField('id', $value);
	}
	
	public function getPrice() {
		return $this->getField('price');
	}
	
	public function setPrice($value) {
		$this->setField('price', $value);
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
					`' . $tableName . '`.`product_category` = ' . $db->quote($this->getCategory()) . '
					`' . $tableName . '`.`product_id` = ' . $db->quote($this->getId()) . '
			';

			// Construct and populate the collection
			$collection->loadBySql($sql);
			foreach ($collection as $element) {
				$element->setProduct_Object($this);
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
		$object->setProductCategory($this->getCategory());
		$object->setProductId($this->getId());
		$object->setProduct_Object($this);
		$this->getProductOrder_Collection()->add($object);
		return $object;
	}
	
	public function removeProductOrder($objectOrId) {
		$removedObject = $this->getProductOrder_Collection()->remove($objectOrId);
		if (is_object($removedObject)) {
			$removedObject->setProductCategory("");
			$removedObject->setProductId("");
			$removedObject->setProduct_Object(null);
		}
		return $removedObject;
	}
	
}

?>