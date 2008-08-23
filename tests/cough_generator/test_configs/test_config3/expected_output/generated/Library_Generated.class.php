<?php

/**
 * This is the base class for Library.
 * 
 * @see Library, CoughObject
 **/
abstract class Library_Generated extends CoughObject {
	
	protected static $db = null;
	protected static $dbName = 'test_cough_object';
	protected static $tableName = 'library';
	protected static $pkFieldNames = array('library_id');
	
	protected $fields = array(
		'library_id' => null,
		'name' => "",
		'last_modified_datetime' => null,
		'creation_datetime' => "",
		'is_retired' => 0,
	);
	
	protected $fieldDefinitions = array(
		'library_id' => array(
			'db_column_name' => 'library_id',
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
		if (is_null(Library::$db)) {
			Library::$db = CoughDatabaseFactory::getDatabase(Library::$dbName);
		}
		return Library::$db;
	}
	
	public static function getDbName() {
		return CoughDatabaseFactory::getDatabaseName(Library::$dbName);
	}
	
	public static function getTableName() {
		return Library::$tableName;
	}
	
	public static function getPkFieldNames() {
		return Library::$pkFieldNames;
	}
	
	// Static Construction (factory) Methods
	
	/**
	 * Constructs a new Library object from
	 * a single id (for single key PKs) or a hash of [field_name] => [field_value].
	 * 
	 * The key is used to pull data from the database, and, if no data is found,
	 * null is returned. You can use this function with any unique keys or the
	 * primary key as long as a hash is used. If the primary key is a single
	 * field, you may pass its value in directly without using a hash.
	 * 
	 * @param mixed $idOrHash - id or hash of [field_name] => [field_value]
	 * @return mixed - Library or null if no record found.
	 **/
	public static function constructByKey($idOrHash, $forPhp5Strict = '') {
		return CoughObject::constructByKey($idOrHash, 'Library');
	}
	
	/**
	 * Constructs a new Library object from custom SQL.
	 * 
	 * @param string $sql
	 * @return mixed - Library or null if exactly one record could not be found.
	 **/
	public static function constructBySql($sql, $forPhp5Strict = '') {
		return CoughObject::constructBySql($sql, 'Library');
	}
	
	/**
	 * Constructs a new Library object after
	 * checking the fields array to make sure the appropriate subclass is
	 * used.
	 * 
	 * No queries are run against the database.
	 * 
	 * @param array $hash - hash of [field_name] => [field_value] pairs
	 * @return Library
	 **/
	public static function constructByFields($hash) {
		return new Library($hash);
	}
	
	public function notifyChildrenOfKeyChange(array $key) {
		foreach ($this->getBook2library_Collection() as $book2library) {
			$book2library->setLibraryId($key['library_id']);
		}
	}
	
	public static function getLoadSql() {
		$tableName = Library::getTableName();
		return '
			SELECT
				`' . $tableName . '`.*
			FROM
				`' . Library::getDbName() . '`.`' . $tableName . '`
		';
	}
	
	// Generated attribute accessors (getters and setters)
	
	public function getLibraryId() {
		return $this->getField('library_id');
	}
	
	public function setLibraryId($value) {
		$this->setField('library_id', $value);
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
	
	public function loadBook2library_Collection() {
		
		// Always create the collection
		$collection = new Book2library_Collection();
		$this->setBook2library_Collection($collection);
		
		// But only populate it if we have key ID
		if ($this->hasKeyId()) {
			$db = Book2library::getDb();
			$tableName = Book2library::getTableName();
			$sql = '
				SELECT
					`' . $tableName . '`.*
					, ' . implode("\n\t\t\t\t\t, ", CoughObject::getFieldAliases('Book', 'Book_Object', 'book')) . '
				FROM
					`' . Book2library::getDbName() . '`.`' . $tableName . '`
					INNER JOIN `' . Book::getDbName() . '`.`' . Book::getTableName() . '` AS `book`
						ON `' . $tableName . '`.`book_id` = `book`.`book_id`
				WHERE
					`' . $tableName . '`.`library_id` = ' . $db->quote($this->getLibraryId()) . '
			';

			// Construct and populate the collection
			$collection->loadBySql($sql);
			foreach ($collection as $element) {
				$element->setLibrary_Object($this);
			}
		}
	}
	
	public function getBook2library_Collection() {
		if (!isset($this->collections['Book2library_Collection'])) {
			$this->loadBook2library_Collection();
		}
		return $this->collections['Book2library_Collection'];
	}
	
	public function setBook2library_Collection($book2libraryCollection) {
		$this->collections['Book2library_Collection'] = $book2libraryCollection;
	}
	
	public function addBook2library(Book2library $object) {
		$object->setLibraryId($this->getLibraryId());
		$object->setLibrary_Object($this);
		$this->getBook2library_Collection()->add($object);
		return $object;
	}
	
	public function removeBook2library($objectOrId) {
		$removedObject = $this->getBook2library_Collection()->remove($objectOrId);
		if (is_object($removedObject)) {
			$removedObject->setLibraryId("");
			$removedObject->setLibrary_Object(null);
		}
		return $removedObject;
	}
	
}

?>