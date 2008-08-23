<?php

/**
 * This is the base class for Book2library.
 * 
 * @see Book2library, CoughObject
 **/
abstract class Book2library_Generated extends CoughObject {
	
	protected static $db = null;
	protected static $dbName = 'test_cough_object';
	protected static $tableName = 'book2library';
	protected static $pkFieldNames = array('book2library_id');
	
	protected $fields = array(
		'book2library_id' => null,
		'book_id' => "",
		'library_id' => "",
		'last_modified_datetime' => null,
		'creation_datetime' => "",
		'is_retired' => 0,
	);
	
	protected $fieldDefinitions = array(
		'book2library_id' => array(
			'db_column_name' => 'book2library_id',
			'is_null_allowed' => false,
			'default_value' => null
		),
		'book_id' => array(
			'db_column_name' => 'book_id',
			'is_null_allowed' => false,
			'default_value' => ""
		),
		'library_id' => array(
			'db_column_name' => 'library_id',
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
	
	protected $objectDefinitions = array(
		'Book_Object' => array(
			'class_name' => 'Book'
		),
		'Library_Object' => array(
			'class_name' => 'Library'
		),
	);
	
	// Static Definition Methods
	
	public static function getDb() {
		if (is_null(Book2library::$db)) {
			Book2library::$db = CoughDatabaseFactory::getDatabase(Book2library::$dbName);
		}
		return Book2library::$db;
	}
	
	public static function getDbName() {
		return CoughDatabaseFactory::getDatabaseName(Book2library::$dbName);
	}
	
	public static function getTableName() {
		return Book2library::$tableName;
	}
	
	public static function getPkFieldNames() {
		return Book2library::$pkFieldNames;
	}
	
	// Static Construction (factory) Methods
	
	/**
	 * Constructs a new Book2library object from
	 * a single id (for single key PKs) or a hash of [field_name] => [field_value].
	 * 
	 * The key is used to pull data from the database, and, if no data is found,
	 * null is returned. You can use this function with any unique keys or the
	 * primary key as long as a hash is used. If the primary key is a single
	 * field, you may pass its value in directly without using a hash.
	 * 
	 * @param mixed $idOrHash - id or hash of [field_name] => [field_value]
	 * @return mixed - Book2library or null if no record found.
	 **/
	public static function constructByKey($idOrHash, $forPhp5Strict = '') {
		return CoughObject::constructByKey($idOrHash, 'Book2library');
	}
	
	/**
	 * Constructs a new Book2library object from custom SQL.
	 * 
	 * @param string $sql
	 * @return mixed - Book2library or null if exactly one record could not be found.
	 **/
	public static function constructBySql($sql, $forPhp5Strict = '') {
		return CoughObject::constructBySql($sql, 'Book2library');
	}
	
	/**
	 * Constructs a new Book2library object after
	 * checking the fields array to make sure the appropriate subclass is
	 * used.
	 * 
	 * No queries are run against the database.
	 * 
	 * @param array $hash - hash of [field_name] => [field_value] pairs
	 * @return Book2library
	 **/
	public static function constructByFields($hash) {
		return new Book2library($hash);
	}
	
	public static function getLoadSql() {
		$tableName = Book2library::getTableName();
		return '
			SELECT
				`' . $tableName . '`.*
			FROM
				`' . Book2library::getDbName() . '`.`' . $tableName . '`
		';
	}
	
	// Generated attribute accessors (getters and setters)
	
	public function getBook2libraryId() {
		return $this->getField('book2library_id');
	}
	
	public function setBook2libraryId($value) {
		$this->setField('book2library_id', $value);
	}
	
	public function getBookId() {
		return $this->getField('book_id');
	}
	
	public function setBookId($value) {
		$this->setField('book_id', $value);
	}
	
	public function getLibraryId() {
		return $this->getField('library_id');
	}
	
	public function setLibraryId($value) {
		$this->setField('library_id', $value);
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
	
	public function loadBook_Object() {
		$this->setBook_Object(Book::constructByKey($this->getBookId()));
	}
	
	public function getBook_Object() {
		if (!isset($this->objects['Book_Object'])) {
			$this->loadBook_Object();
		}
		return $this->objects['Book_Object'];
	}
	
	public function setBook_Object($book) {
		$this->objects['Book_Object'] = $book;
	}
	
	public function loadLibrary_Object() {
		$this->setLibrary_Object(Library::constructByKey($this->getLibraryId()));
	}
	
	public function getLibrary_Object() {
		if (!isset($this->objects['Library_Object'])) {
			$this->loadLibrary_Object();
		}
		return $this->objects['Library_Object'];
	}
	
	public function setLibrary_Object($library) {
		$this->objects['Library_Object'] = $library;
	}
	
	// Generated one-to-many collection loaders, getters, setters, adders, and removers
	
}

?>