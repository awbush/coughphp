<?php

/**
 * This is the base class for Author.
 * 
 * @see Author, CoughObject
 **/
abstract class Author_Generated extends CoughObject {
	
	protected static $db = null;
	protected static $dbName = 'test_cough_object';
	protected static $tableName = 'author';
	protected static $pkFieldNames = array('author_id');
	
	protected $fields = array(
		'author_id' => null,
		'name' => "",
		'last_modified_datetime' => null,
		'creation_datetime' => "",
		'is_retired' => 0,
	);
	
	protected $fieldDefinitions = array(
		'author_id' => array(
			'db_column_name' => 'author_id',
			'is_null_allowed' => false,
			'default_value' => null
		),
		'name' => array(
			'db_column_name' => 'name',
			'is_null_allowed' => false,
			'default_value' => ""
		),
		'last_modified_datetime' => array(
			'db_column_name' => 'last_modified_datetime',
			'is_null_allowed' => true,
			'default_value' => null
		),
		'creation_datetime' => array(
			'db_column_name' => 'creation_datetime',
			'is_null_allowed' => false,
			'default_value' => ""
		),
		'is_retired' => array(
			'db_column_name' => 'is_retired',
			'is_null_allowed' => false,
			'default_value' => 0
		),
	);
	
	protected $objectDefinitions = array();
	
	// Static Definition Methods
	
	public static function getDb() {
		if (is_null(Author::$db)) {
			Author::$db = CoughDatabaseFactory::getDatabase(Author::$dbName);
		}
		return Author::$db;
	}
	
	public static function getDbName() {
		return CoughDatabaseFactory::getDatabaseName(Author::$dbName);
	}
	
	public static function getTableName() {
		return Author::$tableName;
	}
	
	public static function getPkFieldNames() {
		return Author::$pkFieldNames;
	}
	
	// Static Construction (factory) Methods
	
	/**
	 * Constructs a new Author object from
	 * a single id (for single key PKs) or a hash of [field_name] => [field_value].
	 * 
	 * The key is used to pull data from the database, and, if no data is found,
	 * null is returned. You can use this function with any unique keys or the
	 * primary key as long as a hash is used. If the primary key is a single
	 * field, you may pass its value in directly without using a hash.
	 * 
	 * @param mixed $idOrHash - id or hash of [field_name] => [field_value]
	 * @return mixed - Author or null if no record found.
	 **/
	public static function constructByKey($idOrHash, $forPhp5Strict = '') {
		return CoughObject::constructByKey($idOrHash, 'Author');
	}
	
	/**
	 * Constructs a new Author object from custom SQL.
	 * 
	 * @param string $sql
	 * @return mixed - Author or null if exactly one record could not be found.
	 **/
	public static function constructBySql($sql, $forPhp5Strict = '') {
		return CoughObject::constructBySql($sql, 'Author');
	}
	
	/**
	 * Constructs a new Author object after
	 * checking the fields array to make sure the appropriate subclass is
	 * used.
	 * 
	 * No queries are run against the database.
	 * 
	 * @param array $hash - hash of [field_name] => [field_value] pairs
	 * @return Author
	 **/
	public static function constructByFields($hash) {
		return new Author($hash);
	}
	
	public function notifyChildrenOfKeyChange(array $key) {
		foreach ($this->getBook_Collection() as $book) {
			$book->setAuthorId($key['author_id']);
		}
	}
	
	public static function getLoadSql() {
		$tableName = Author::getTableName();
		return '
			SELECT
				`' . $tableName . '`.*
			FROM
				`' . Author::getDbName() . '`.`' . $tableName . '`
		';
	}
	
	// Generated attribute accessors (getters and setters)
	
	public function getAuthorId() {
		return $this->getField('author_id');
	}
	
	public function setAuthorId($value) {
		$this->setField('author_id', $value);
	}
	
	public function getName() {
		return $this->getField('name');
	}
	
	public function setName($value) {
		$this->setField('name', $value);
	}
	
	public function getLastModifiedDatetime() {
		return $this->getField('last_modified_datetime');
	}
	
	public function setLastModifiedDatetime($value) {
		$this->setField('last_modified_datetime', $value);
	}
	
	public function getCreationDatetime() {
		return $this->getField('creation_datetime');
	}
	
	public function setCreationDatetime($value) {
		$this->setField('creation_datetime', $value);
	}
	
	public function getIsRetired() {
		return $this->getField('is_retired');
	}
	
	public function setIsRetired($value) {
		$this->setField('is_retired', $value);
	}
	
	// Generated one-to-one accessors (loaders, getters, and setters)
	
	// Generated one-to-many collection loaders, getters, setters, adders, and removers
	
	public function loadBook_Collection() {
		
		// Always create the collection
		$collection = new Book_Collection();
		$this->setBook_Collection($collection);
		
		// But only populate it if we have key ID
		if ($this->hasKeyId()) {
			$db = Book::getDb();
			$tableName = Book::getTableName();
			$sql = '
				SELECT
					`' . $tableName . '`.*
				FROM
					`' . Book::getDbName() . '`.`' . $tableName . '`
				WHERE
					`' . $tableName . '`.`author_id` = ' . $db->quote($this->getAuthorId()) . '
			';

			// Construct and populate the collection
			$collection->loadBySql($sql);
			foreach ($collection as $element) {
				$element->setAuthor_Object($this);
			}
		}
	}
	
	public function getBook_Collection() {
		if (!isset($this->collections['Book_Collection'])) {
			$this->loadBook_Collection();
		}
		return $this->collections['Book_Collection'];
	}
	
	public function setBook_Collection($bookCollection) {
		$this->collections['Book_Collection'] = $bookCollection;
	}
	
	public function addBook(Book $object) {
		$object->setAuthorId($this->getAuthorId());
		$object->setAuthor_Object($this);
		$this->getBook_Collection()->add($object);
		return $object;
	}
	
	public function removeBook($objectOrId) {
		$removedObject = $this->getBook_Collection()->remove($objectOrId);
		if (is_object($removedObject)) {
			$removedObject->setAuthorId(0);
			$removedObject->setAuthor_Object(null);
		}
		return $removedObject;
	}
	
}

?>