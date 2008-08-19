<?php

/**
 * This is the base class for Book.
 * 
 * @see Book, CoughObject
 **/
abstract class Book_Generated extends CoughObject {
	
	protected static $db = null;
	protected static $dbName = 'test_cough_object';
	protected static $tableName = 'book';
	protected static $pkFieldNames = array('book_id');
	
	protected $fields = array(
		'book_id' => null,
		'title' => "",
		'author_id' => 0,
		'introduction' => "",
		'last_modified_datetime' => null,
		'creation_datetime' => "",
		'is_retired' => 0,
	);
	
	protected $fieldDefinitions = array(
		'book_id' => array(
			'db_column_name' => 'book_id',
			'is_null_allowed' => false,
			'default_value' => null
		),
		'title' => array(
			'db_column_name' => 'title',
			'is_null_allowed' => false,
			'default_value' => ""
		),
		'author_id' => array(
			'db_column_name' => 'author_id',
			'is_null_allowed' => false,
			'default_value' => 0
		),
		'introduction' => array(
			'db_column_name' => 'introduction',
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
		'Author_Object' => array(
			'class_name' => 'Author'
		),
	);
	
	// Static Definition Methods
	
	public static function getDb() {
		if (is_null(Book::$db)) {
			Book::$db = CoughDatabaseFactory::getDatabase(Book::$dbName);
		}
		return Book::$db;
	}
	
	public static function getDbName() {
		return CoughDatabaseFactory::getDatabaseName(Book::$dbName);
	}
	
	public static function getTableName() {
		return Book::$tableName;
	}
	
	public static function getPkFieldNames() {
		return Book::$pkFieldNames;
	}
	
	// Static Construction (factory) Methods
	
	/**
	 * Constructs a new Book object from
	 * a single id (for single key PKs) or a hash of [field_name] => [field_value].
	 * 
	 * The key is used to pull data from the database, and, if no data is found,
	 * null is returned. You can use this function with any unique keys or the
	 * primary key as long as a hash is used. If the primary key is a single
	 * field, you may pass its value in directly without using a hash.
	 * 
	 * @param mixed $idOrHash - id or hash of [field_name] => [field_value]
	 * @return mixed - Book or null if no record found.
	 **/
	public static function constructByKey($idOrHash, $forPhp5Strict = '') {
		return CoughObject::constructByKey($idOrHash, 'Book');
	}
	
	/**
	 * Constructs a new Book object from custom SQL.
	 * 
	 * @param string $sql
	 * @return mixed - Book or null if exactly one record could not be found.
	 **/
	public static function constructBySql($sql, $forPhp5Strict = '') {
		return CoughObject::constructBySql($sql, 'Book');
	}
	
	/**
	 * Constructs a new Book object after
	 * checking the fields array to make sure the appropriate subclass is
	 * used.
	 * 
	 * No queries are run against the database.
	 * 
	 * @param array $hash - hash of [field_name] => [field_value] pairs
	 * @return Book
	 **/
	public static function constructByFields($hash) {
		return new Book($hash);
	}
	
	public function notifyChildrenOfKeyChange(array $key) {
		foreach ($this->getBook2library_Collection() as $book2library) {
			$book2library->setBookId($key['book_id']);
		}
	}
	
	public static function getLoadSql() {
		$tableName = Book::getTableName();
		return '
			SELECT
				`' . $tableName . '`.*
				, ' . implode("\n\t\t\t\t, ", CoughObject::getFieldAliases('Author', 'Author_Object', 'author')) . '
			FROM
				`' . Book::getDbName() . '`.`' . $tableName . '`
				INNER JOIN `' . Author::getDbName() . '`.`' . Author::getTableName() . '` AS `author`
					ON `' . $tableName . '`.`author_id` = `author`.`author_id`
		';
	}
	
	// Generated attribute accessors (getters and setters)
	
	public function getBookId() {
		return $this->getField('book_id');
	}
	
	public function setBookId($value) {
		$this->setField('book_id', $value);
	}
	
	public function getTitle() {
		return $this->getField('title');
	}
	
	public function setTitle($value) {
		$this->setField('title', $value);
	}
	
	public function getAuthorId() {
		return $this->getField('author_id');
	}
	
	public function setAuthorId($value) {
		$this->setField('author_id', $value);
	}
	
	public function getIntroduction() {
		return $this->getField('introduction');
	}
	
	public function setIntroduction($value) {
		$this->setField('introduction', $value);
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
	
	public function loadAuthor_Object() {
		$this->setAuthor_Object(Author::constructByKey($this->getAuthorId()));
	}
	
	public function getAuthor_Object() {
		if (!isset($this->objects['Author_Object'])) {
			$this->loadAuthor_Object();
		}
		return $this->objects['Author_Object'];
	}
	
	public function setAuthor_Object($author) {
		$this->objects['Author_Object'] = $author;
	}
	
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
					, ' . implode("\n\t\t\t\t\t, ", CoughObject::getFieldAliases('Library', 'Library_Object', 'library')) . '
				FROM
					`' . Book2library::getDbName() . '`.`' . $tableName . '`
					INNER JOIN `' . Library::getDbName() . '`.`' . Library::getTableName() . '` AS `library`
						ON `' . $tableName . '`.`library_id` = `library`.`library_id`
				WHERE
					`' . $tableName . '`.`book_id` = ' . $db->quote($this->getBookId()) . '
			';

			// Construct and populate the collection
			$collection->loadBySql($sql);
			foreach ($collection as $element) {
				$element->setBook_Object($this);
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
		$object->setBookId($this->getBookId());
		$object->setBook_Object($this);
		$this->getBook2library_Collection()->add($object);
		return $object;
	}
	
	public function removeBook2library($objectOrId) {
		$removedObject = $this->getBook2library_Collection()->remove($objectOrId);
		if (is_object($removedObject)) {
			$removedObject->setBookId("");
			$removedObject->setBook_Object(null);
		}
		return $removedObject;
	}
	
}

?>