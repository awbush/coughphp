<?php

/**
 * CoughObject is the foundation for which all other "Model" / "ORM" classes
 * extend.  There will usually be one class extending CoughObject for each table
 * in the database that an ORM is needed for.
 * 
 * It might be wise to add your own AppModel / AppCoughObject class that extends
 * CoughObject and have all your classes extend that one; this way you can add
 * custom functionality to Cough without modifying the Cough source.
 * 
 * @package CoughPHP
 **/
abstract class CoughObject {

	/**
	 * An array of all the columns in the database, including the primary key
	 * column and name columns.
	 *
	 * Format of "field_name" => attributes
	 *
	 * @var array
	 * @see defineFields()
	 * @todo Tom: Why?
	 **/
	protected $fieldDefinitions = array();
	
	/**
	 * The primary key field names
	 *
	 * Override in sub class.
	 * 
	 * @var array
	 * @see getPkFieldNames(), getPk(), defineFields()
	 **/
	protected $pkFieldNames = array();
	
	/**
	 * An array of derived field definitions
	 * 
	 * Format of "derived_field_name" => attributes
	 *
	 * @var array
	 * @see defineDerivedFields()
	 **/
	protected $derivedFieldDefinitions = array();
	
	/**
	 * An array of all the objects and their attributes.
	 *
	 * The information is used by CoughObject to instantiate and load the
	 * objects.
	 *
	 * Format of [objectName] => [array of attributes]
	 *
	 * TODO: Document that array of attributes. For now just look at the
	 * woc_Product_Generated class (at the defineObjects() function).
	 *
	 * @var array
	 **/
	protected $objectDefinitions = array();
	
	/**
	 * The alias name of the database, which is used to ask the
	 * {@link CoughDatabaseFactory} for a database object.
	 * 
	 * Override in sub class.
	 * 
	 * @var string
	 **/
	protected $dbAlias = null;
	
	/**
	 * The name of the database the table is in.
	 * 
	 * Override in sub class.
	 * 
	 * @var string
	 **/
	protected $dbName = null;
	
	/**
	 * The name of table the object maps to.
	 *
	 * Override in sub class.
	 * 
	 * @var string
	 **/
	protected $tableName;

	/**
	 * An array of all the currently initialized or set fields.
	 *
	 * Format of "field_name" => value
	 *
	 * @var array
	 * @see getField(), getFields(), getFieldsWithoutPk(), setField(), setFields()
	 * @todo Tom: Why?
	 **/
	protected $fields = array();

	/**
	 * An array of fields that have been modified.
	 *
	 * @var array
	 * @see getModifiedFields(), setModifiedField(), resetModified()
	 **/
	protected $modifiedFields = array();
	
	/**
	 * An array of derived fields (read-only, as in not saved back to the
	 * database).
	 * 
	 * Format of "derived_field_name" => value
	 *
	 * @var array
	 * @see getDerivedField(), setDerivedField(), defineDerivedFields()
	 **/
	protected $derivedFields = array();
	
	/**
	 * An array of all the loaded collections in form [collectionName] => [CoughCollection]
	 * 
	 * @var array
	 * @see getCollection(), loadCollection(), saveLoadedCollections(), isCollectionLoaded()
	 **/
	protected $collections = array();
	
	/**
	 * An array of all the loaded objects in form [objectName] => [CoughObject]
	 * 
	 * @var array
	 * @see getObject(), loadObject(), saveLoadedObjects(), isObjectLoaded()
	 **/
	protected $objects = array();

	/**
	 * A reference to the DatabaseConnector object; used for executing the
	 * queries.
	 *
	 * @var DatabaseConnector
	 **/
	protected $db;
	
	/**
	 * Stores whether or not the object has been deleted from the database.
	 * 
	 * Save will do nothing if an object has been deleted...
	 *
	 * @var boolean
	 * @see delete(), save()
	 **/
	protected $isDeleted = false;
	
	/**
	 * Stores whether or not the object is new (i.e. not in database yet).
	 * 
	 * Save will perform an INSERT if $isNew is true, otherwise it will perform
	 * an UPDATE as long as hasKeyId() also returns true.
	 * 
	 * Note that isNew() = !isInflated(). That is, any time an object is
	 * inflated, it is considered to be synced with the database, and therefore
	 * not new.
	 *
	 * @var boolean
	 * @see save(), isNew(), isInflated()
	 **/
	protected $isNew = true;
	
	/**
	 * Stores validation errors set by `validateData` function.
	 * 
	 * Format of "field_name" => "Error Text"
	 *
	 * @var array
	 * @see clearValidationErrors()
	 **/
	protected $validationErrors = array();
	
	/**
	 * Keep track of whether or not data has been validated.
	 *
	 * @var boolean
	 * @see clearValidationErrors()
	 **/
	protected $validatedData = false;

	// ----------------------------------------------------------------------------------------------
	// CONSTRUCTORS and INITIALIZATION METHODS block BEGINS
	// ----------------------------------------------------------------------------------------------

	/**
	 * Construct an empty CoughObject or an object with pre-initialized data.
	 * 
	 * @param $fieldsOrID - initializes the object with the fields or id (does not query the database)
	 * @return void
	 **/
	public function __construct($fieldsOrID = array(), $relatedEntities = array()) {
		// before chaining to construct, make sure you override initializeDefinitions() within the subclass
		//	and then invoke initializeDefinitions() in the constructor method.
		$this->initializeDefinitions();
		
		// Get our reference to the database object
		$this->db = CoughDatabaseFactory::getDatabase($this->dbAlias);
		$this->db->selectDb($this->dbName);
		
		// Initialize fields and related entities
		$this->inflate($fieldsOrID, $relatedEntities);
		
		// Allow post construction specific code
		$this->finishConstruction();
	}
	
	/**
	 * Sets the cough object's basic identity:
	 * 
	 *    - {@link $objectDefinitions}
	 *    - default values
	 *
	 * @return void
	 **/
	protected function initializeDefinitions() {
		$this->defineDbConfig();
		$this->defineFields();
		$this->defineDerivedFields();
		$this->defineObjects();
	}
	
	/**
	 * Override in sub-class to set $dbName and $tableName via code.
	 *
	 * @return void
	 **/
	protected function defineDbConfig() {}

	/**
	 * Override in sub-class to define fields the object possesses, including
	 * $pkFieldNames.
	 * 
	 * @return void
	 **/
	protected function defineFields() {}
	
	/**
	 * Override in sub-class to define derived fields the object may possess.
	 * 
	 * @return void
	 **/
	protected function defineDerivedFields() {}

	/**
	 * Override in sub-class to define objects the object possesses.
	 *
	 * @return void
	 **/
	protected function defineObjects() {}
	
	/**
	 * Called at the end of __construct(). Override for special construction
	 * behavior that is dependent on the object's state.
	 * 
	 * @return void
	 **/
	protected function finishConstruction() {}
	
	/**
	 * Clone only the non-primary key fields.
	 * 
	 * Usage:
	 *     $newProduct = clone $product;
	 *
	 * @return void
	 * @author Anthony Bush
	 * @author Lewis Zhang
	 **/
	public function __clone() {
		// Reset all attributes of the cloned object, except any non-primary key field.
		$className = get_class($this);
		$freshObject = new $className();
		foreach ($freshObject as $key => $value) {
			if ($key != 'fields') {
				$this->$key = $value;
			}
		}
		
		// Mark all fields as having been modified (except key ID) so that a call to save() will complete the clone.
		$this->setKeyId(null);
		$this->resetModified();
		foreach (array_keys($this->getFieldsWithoutPk()) as $fieldName) {
			$this->setModifiedField($fieldName);
		}
	}
	
	/**
	 * This will compare two CoughObjects and return true if they are of the
	 * same type and have the same field values (excluding the primary key).
	 * 
	 * Feel free to override this in sub classes for customized comparison.
	 * 
	 * @return boolean
	 * @author Anthony Bush
	 **/
	public function isEqualTo($coughObject) {
		if (get_class($this) == get_class($coughObject)) {
			if ($this->getFieldsWithoutPk() == $coughObject->getFieldsWithoutPk()) {
				return true;
			}
		}
		return false;
	}

	// ----------------------------------------------------------------------------------------------
	// CONSTRUCTORS and INITIALIZATION METHODS block ENDS
	// ----------------------------------------------------------------------------------------------


	// ----------------------------------------------------------------------------------------------
	// GETTORS AND SETTORS block BEGINS
	// ----------------------------------------------------------------------------------------------
	
	/**
	 * Sets the object's primary key id to the passed value.
	 * 
	 * If key is multi-key:
	 * 
	 *     * Call this with an array of [field_name] => [field_value] pairs to
	 *       set all values uniquely.
	 *     
	 *     * Call this with a non-array value to set all keys to the same value.
	 * 
	 * @param mixed $id
	 * @return void
	 * @author Anthony Bush
	 **/
	public function setKeyId($id) {
		if (!is_array($id)) {
			$key = array();
			foreach ($this->getPkFieldNames() as $fieldName) {
				$key[$fieldName] = $id;
			}
		} else {
			$key = $id;
		}
		$this->notifyChildrenOfKeyChange($key);
		$this->setFields($key);
	}
	
	/**
	 * This method's job is to notify related collections (if any) of the key change.
	 * 
	 * For example, if the schema is "order has many order lines" then on the
	 * Order object the `notifyChildrenOfKeyChange` function might change the
	 * order_id on all the order_line entities.  The code might look like:
	 * 
	 *     foreach ($this->getOrderLine_Collection() as $orderLine) {
	 *         $orderLine->setOrderId($key['order_id']);
	 *     }
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function notifyChildrenOfKeyChange(array $pk) {
		// override this below
	}
	
	/**
	 * Returns whether or not the object is new (i.e. not in persistent storage)
	 *
	 * This method returns the opposite of {@link isInflated()}
	 * 
	 * @return boolean
	 * @author Anthony Bush
	 * @see isInflated()
	 **/
	public function isNew() {
		return $this->isNew;
	}
	
	/**
	 * Returns whether or not the object is inflated (i.e. pulled from
	 * persistent storage)
	 * 
	 * This method returns the opposite of {@link isNew()}
	 * 
	 * @return void
	 * @author Anthony Bush
	 * @see isNew()
	 **/
	public function isInflated() {
		return !$this->isNew;
	}
	
	/**
	 * Returns the current value of the object's primary key id.
	 * 
	 * If the key is multi-key, this returns a unique string identifying itself
	 * (a concatenation of the fields making up the PK).
	 *
	 * @return mixed - string for multi-key PKs, integer for single-key PKs
	 * @author Anthony Bush
	 **/
	public function getKeyId() {
		if (count($this->pkFieldNames) == 1) {
			return $this->fields[$this->pkFieldNames[0]];
		} else {
			return implode(',', $this->getPk());
		}
	}
	
	/**
	 * Returns the primary key as an array of [field_name] => field_value pairs
	 *
	 * @return array
	 * @author Anthony Bush
	 * @since 2007-07-06
	 **/
	public function getPk() {
		$pk = array();
		foreach ($this->pkFieldNames as $fieldName) {
			$pk[$fieldName] = $this->fields[$fieldName];
		}
		return $pk;
	}
	
	/**
	 * Returns true if all the key fields that make up the primary key are set
	 * to non-null values.
	 *
	 * @return boolean
	 * @author Anthony Bush
	 * @todo If NULL is a valid value for a PK, this function needs an update.
	 **/
	public function hasKeyId() {
		// Must have at least one field marked as PK.
		if (empty($this->pkFieldNames)) {
			return false;
		}
		
		// All PK fields must be initialized
		foreach ($this->pkFieldNames as $fieldName) {
			if (!isset($this->fields[$fieldName])) {
				return false;
			}
		}
		
		// Search for non-set key exhausted.
		return true;
	}
	
	/**
	 * Returns the object's fields as an array of [key] => [value] pairs.
	 * 
	 * Only the fields that directly correspond to columns in the database are
	 * returned. This means no:
	 * 
	 *     - derived fields
	 *     - join fields
	 *     - objects for one-to-one or one-to-many relationships
	 * 
	 * It's useful if you need to pass on just the data of the object but not
	 * the object itself, either for displaying in an HTML form, storing in
	 * the session for later retrieval, etc.
	 * 
	 * For example, you can store the raw data in a session and reconstruct the
	 * object with it later, so you don't waste space in the session, and you
	 * don't have to re-pull the data from the database:
	 * 
	 *     // E.g. save user data when they log in.
	 *     $_SESSION['User'] = $user->getFields();
	 *     
	 *     // Then reconstruct the object later:
	 *     $user = new User($_SESSION['User']);
	 * 
	 * @return array
	 * @author Anthony Bush
	 **/
	public function getFields() {
		return $this->fields;
	}
	
	/**
	 * Get all non-primary key related fields and their values.
	 *
	 * @return array
	 * @author Anthony Bush
	 * @since 2007-07-06
	 **/
	public function getFieldsWithoutPk() {
		$fields = $this->getFields();
		foreach ($this->getPkFieldNames() as $fieldName) {
			unset($fields[$fieldName]);
		}
		return $fields;
	}
	
	/**
	 * Get the primary key field names as an array.
	 *
	 * @return array
	 * @author Anthony Bush
	 * @since 2007-07-06
	 **/
	public function getPkFieldNames() {
		return $this->pkFieldNames;
	}
	
	/**
	 * Returns the current value of the requested field name.
	 *
	 * @return mixed
	 **/
	protected function getField($fieldName) {
		if (isset($this->fields[$fieldName])) {
			return ($this->fields[$fieldName]);
		} else {
			return null;
		}
	}
	
	/**
	 * Sets the current value of $fieldName to $value.
	 * 
	 * @param string $fieldName
	 * @param mixed $value
	 * @return void
	 **/
	protected function setField($fieldName, $value) {
		$this->setModifiedField($fieldName);
		$this->fields[$fieldName] = $value;
	}
	
	/**
	 * Sets the current value of the all the object's defined fields equal to the values passed in the $fields associative array.
	 * 
	 * Note: if a field passed in the fields associative array isn't in the class's defined fields, it will NOT BE SET.
	 * 
	 * @param array $fields - format of [field_name] => [new_value]
	 * @return void
	 * @todo Anthony: Make this like inflate? -- make it recursive, e.g. if 'account' => array('name' => 'Bob') is passed in, then do getObject('account')->setFields(array('name' => 'Bob')); OR maybe we need setFieldsIfDifferent(array('name' => array('new' => 'Bob', 'old' => 'Fred')))
	 **/
	public function setFields($fields) {
		foreach ($fields as $fieldName => $fieldValue) {
			if (isset($this->fieldDefinitions[$fieldName])) {
				$this->setField($fieldName, $fieldValue);
			} else if (isset($this->derivedFieldDefinitions[$fieldName])) {
				$this->setDerivedField($fieldName, $fieldValue);
			}
		}
	}
	
	/**
	 * Sets fields in the given hash, but only if they new values are different
	 * from the existing values
	 * 
	 * @param array $fields - format of [field_name] => [new_value]
	 * @return void
	 * @author Anthony Bush
	 * @since 2007-08-02
	 **/
	public function setFieldsIfDifferent($fields) {
		foreach ($fields as $fieldName => $fieldValue) {
			if ($fieldValue != $this->getField($fieldName)) {
				$this->setField($fieldName, $fieldValue);
			}
		}
	}
	
	/**
	 * Sets a read-only field; It's usually a derived field from a complex
	 * SQL query such as when overriding the getLoadSql() function.
	 *
	 * @param string $fieldName - the derived field name to set
	 * @param mixed $fieldValue - the value to store
	 * @return void
	 * @see getLoadSql()
	 * @author Anthony Bush
	 **/
	protected function setDerivedField($fieldName, $fieldValue) {
		$this->derivedFields[$fieldName] = $fieldValue;
	}
	
	/**
	 * Returns the specified derived field name.
	 * 
	 * @param string $fieldName - the derived field name to retrieve
	 * @return mixed - the value of the specified field
	 * @see setDerivedField(), getLoadSql()
	 * @author Anthony Bush
	 **/
	public function getDerivedField($fieldName) {
		if (isset($this->derivedFields[$fieldName])) {
			return $this->derivedFields[$fieldName];
		} else {
			return null;
		}
	}
	
	// ----------------------------------------------------------------------------------------------
	// GETTORS AND SETTORS block ENDS
	// ----------------------------------------------------------------------------------------------


	// ----------------------------------------------------------------------------------------------
	// object database methods / collection handling methods block BEGINS
	// ----------------------------------------------------------------------------------------------
	
	/**
	 * Retrieves the object's data from the database, loading it into memory.
	 * 
	 * @return boolean - whether or not load was able to find a record in the database.
	 * @author Anthony Bush
	 **/
	public function load() {
		return $this->loadBySql($this->getLoadSql());
	}
	
	/**
	 * Returns the current SQL statement that the {@link load()} method should
	 * run.
	 * 
	 * Override this in sub classes for custom SQL.
	 *
	 * @return mixed - string of SQL or empty string if no SQL to run.
	 * @author Anthony Bush
	 **/
	protected function getLoadSql() {
		if ($this->hasKeyId()) {
			$sql = $this->getLoadSqlWithoutWhere() . ' WHERE ' . $this->db->buildWhereSql($this->getPk());
		} else {
			$sql = '';
		}
		return $sql;
	}
	
	/**
	 * Returns the core SQL statement that other load methods should build upon
	 * (no WHERE clause is returned). This allows other functions (like
	 * {@link loadByCriteria()} and even other collections) to share the same
	 * SELECT and FROM portions of the SQL.
	 * 
	 * Override this in sub classes for custom SQL. Trailing white-space is not
	 * required. The SQL should be runnable on it's own (i.e. no syntax errors)
	 *
	 * @return string
	 * @author Anthony Bush
	 * @todo Consider moving to something like prepared statements.
	 **/
	public function getLoadSqlWithoutWhere() {
		return 'SELECT * FROM `' . $this->dbName . '`.`' . $this->tableName . '`';
	}
	
	/**
	 * Provides a way to load by an array of "key" => "value" pairs.
	 *
	 * @param array $where - an array of "key" => "value" pairs to search for
	 * @param boolean $additionalSql - add ORDER BYs and LIMITs here.
	 * @return boolean - true if initialized object with data, false otherwise.
	 * @author Anthony Bush
	 **/
	public function loadByCriteria($where = array(), $additionalSql = '') {
		if ( ! empty($where)) {
			$sql = $this->getLoadSqlWithoutWhere() . ' WHERE ' . $this->db->buildWhereSql($where) . ' ' . $additionalSql;
			return $this->loadBySql($sql);
		}
		return false;
	}
	
	/**
	 * Provides a way to load by custom SQL.
	 *
	 * @param string $sql
	 * @return boolean - true if initialized object with data, false otherwise.
	 * @author Anthony Bush
	 **/
	public function loadBySql($sql) {
		$inflated = false;
		if ( ! empty($sql)) {
			$this->db->selectDb($this->dbName);
			$result = $this->db->query($sql);
			if ($result->getNumRows() == 1) {
				$this->inflate($result->getRow());
				$inflated = true;
			} else {
				// load failed because the unique dataset couldn't be selected
			}
			$result->freeResult();
		} else {
			// load failed because no SQL was given
		}
		return $inflated;
	}

	/**
	 * Clear the list of modified fields and other modified flags.
	 *
	 * @return void
	 **/
	protected function resetModified() {
		$this->modifiedFields = array();
	}

	/**
	 * Add a field to the list of modified fields
	 *
	 * @param string $fieldName
	 * @return void
	 **/
	protected function setModifiedField($fieldName) {
		$this->modifiedFields[$fieldName] = $this->getField($fieldName);
	}

	/**
	 * Get a list of all of this object's modified values.
	 *
	 * @return associative array - all modified values in [field_name] => [old_value] form
	 **/
	protected function getModifiedFields() {
		return $this->modifiedFields;
	}
	
	/**
	 * Returns whether or not there are modified fields.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function hasModifiedFields() {
		if (!empty($this->modifiedFields)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Creates a new entry if needed, otherwise it updates an existing one.
	 * During an update, it only updates the modified fields.
	 * During an insert, it only inserts modified fields (leaving defaults up to
	 * the database server).
	 * 
	 * @return boolean - the result of the create/update.
	 * @author Anthony Bush
	 **/
	public function save() {
		
		// Don't save if deleted.
		if ($this->isDeleted()) {
			return false;
		}
		
		// Check for valid data.
		if ( ! $this->validateData()) {
			return false;
		}
		
		// Save self first, in case the PK is needed for remaining saves.
		if ($this->shouldInsert()) {
			$result = $this->insert();
		} else {
			$result = $this->update();
		}
		$this->isNew = false;

		$this->saveLoadedCollections();
		
		$this->resetModified();
		
		return $result;
	}

	/**
	 * Indicates whether or not a save should insert a new record or update an
	 * existing one.
	 *
	 * @return boolean - true = insert new, false = update existing one
	 * @author Anthony Bush
	 **/
	protected function shouldInsert() {
		if (!$this->hasKeyId() || $this->isNew()) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Saves all the loaded objects (if it wasn't loaded there would be
	 * nothing to save)
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function saveLoadedObjects() {
		foreach ($this->objects as $object) {
			$object->save();
		}
	}
	
	/**
	 * Saves all the loaded collections (if it wasn't loaded there would be
	 * nothing to save)
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function saveLoadedCollections() {
		foreach ($this->collections as $collection) {
			$collection->save();
		}
	}
	
	/**
	 * Inserts a new row to the database and sets the object's key id with the
	 * returned database insert id.
	 * 
	 * If the object has a multi-key PK, then the key is not set after insert.
	 * 
	 * By default, only values that have been modified are used in the INSERT
	 * statement, leaving it up to the database to set default values for the
	 * other fields. To change this, override the {@link getInsertFields()}
	 * method.
	 * 
	 * @return boolean
	 * @author Anthony Bush
	 **/
	protected function insert() {
		
		$fields = $this->getInsertFields();
		
		$this->db->selectDb($this->dbName);
		$result = $this->db->insert($this->tableName, $fields);
		if ($result) {
			if (!$this->hasKeyId()) {
				$this->setKeyId($result);
			}
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Returns the fields that should be used for inserting. This logic is
	 * separate from the insert method to make it easy to override the
	 * behavior.
	 *
	 * @return associative array [field_name] => [field_value]
	 * @author Anthony Bush
	 **/
	protected function getInsertFields() {
		$fields = array();
		foreach ($this->getModifiedFields() as $fieldName => $oldValue) {
			$fields[$fieldName] = $this->getField($fieldName);
		}
		return $fields;
	}
	
	/**
	 * Updates the database with modified values, if any.
	 *
	 * @return boolean - true
	 **/
	protected function update() {
		$fields = $this->getUpdateFields();
		if (!empty($fields)) {
			$this->db->selectDb($this->dbName);
			$this->db->update($this->tableName, $fields, null, $this->getPk());
		}
		return true;
	}
	
	/**
	 * Returns the fields that should be used for updating. This logic is
	 * separate from the update method to make it easy to override the
	 * behavior.
	 *
	 * @return associative array [field_name] => [field_value]
	 * @author Anthony Bush
	 **/
	protected function getUpdateFields() {
		$fields = array();
		foreach ($this->getModifiedFields() as $fieldName => $oldValue) {
			$fields[$fieldName] = $this->getField($fieldName);
		}
		return $fields;
	}
	
	/**
	 * Deletes the record from the database, if hasKeyId returns true.
	 * 
	 * Override this for special delete functionality. For example, if an object
	 * should never be deleted, but instead just retired, then override this
	 * to look like:
	 * 
	 *     $this->setIsRetired(true);
	 *     $this->save();
	 * 
	 * Then just make sure the load queries include is_retired = 0 in the WHERE
	 * clause so that the item is not pulled from the database (i.e. it appears
	 * deleted, but the data is still there for historical reference/backup.)
	 * 
	 * Usually, the coder will be the only one to call delete, but Cough will
	 * call it on join objects when removing objects in a many-to-many
	 * collection.
	 *
	 * @return boolean - whether or not the delete was executed.
	 * @author Anthony Bush
	 **/
	public function delete() {
		if ($this->hasKeyId()) {
			$this->db->delete($this->tableName, $this->getPk());
			$this->isDeleted = true;
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Specifies whether or not the object has been deleted from the database.
	 *
	 * @return boolean
	 * @author Anthony Bush
	 **/
	public function isDeleted() {
		return $this->isDeleted;
	}
	
	// ----------------------------------------------------------------------------------------------
	// object database methods / collection handling methods block ENDS
	// ----------------------------------------------------------------------------------------------
		
	/**
	 * Validates data stored in the model. It is called automatically by `save`,
	 * which will return false if this function sets any errors.
	 * 
	 * If you pass it nothing, it will validate with the current data stored in
	 * the object.
	 * 
	 * @param array $data - the data  to validate in [field_name] => [value] form.
	 * @return boolean - result of {@link isDataValid()}
	 * @see isDataValid(), getValidationErrors()
	 * @author Anthony Bush
	 **/
	public function validateData(&$data = null) {
		if (!$this->validatedData) {
			if (is_null($data)) {
				$this->doValidateData($this->fields);
			} else {
				$this->doValidateData($data);
			}
		}
		$this->validatedData = true;
		return $this->isDataValid();
	}
	
	/**
	 * Do the actual data validation. Override in sub classes.
	 *
	 * @author Anthony Bush
	 * @see validateData()
	 **/
	protected function doValidateData(&$data) {
	}
	
	/**
	 * Invalidates a field with an optional message.
	 * 
	 * @param string $fieldName - field to invalidate
	 * @param string $msg - optional message to store of why data is invalid.
	 * @return void
	 * @author Anthony Bush
	 **/
	public function invalidateField($fieldName, $msg = '') {
		$this->validationErrors[$fieldName] = $msg;
	}
	
	/**
	 * Returns true if the field is valid (i.e. no validation errors set),
	 * otherwise it returns false.
	 *
	 * @param string $fieldName - the field name to load.
	 * @return boolean
	 * @author Anthony Bush
	 **/
	public function isFieldValid($fieldName) {
		if (isset($this->validationErrors[$fieldName])) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Returns true if the data is valid (i.e. no validation errors set),
	 * otherwise it returns false.
	 *
	 * @return boolean
	 * @author Anthony Bush
	 **/
	public function isDataValid() {
		if (empty($this->validationErrors)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Returns the validation errors set by `valdiateData()`.
	 *
	 * @return array
	 * @author Anthony Bush
	 **/
	public function getValidationErrors() {
		return $this->validationErrors;
	}
	
	/**
	 * Clear validation status.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function clearValidationErrors() {
		$this->validatedData = false;
		$this->validationErrors = array();
	}
	
	public static function titleCase($underscoredString) {
		return str_replace(' ', '', ucwords(str_replace('_', ' ', $underscoredString)));
	}
	
	public static function underscore($camelCasedString) {
		return preg_replace('/_i_d$/', '_id', strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $camelCasedString)));
	}
	
	/**
	 * Returns object to it's own state, except what you want to keep. By
	 * default it empties everything; overridden version only empties yours.
	 *
	 * @return void
	 * @todo Tom: Why this function? (provide good example so it can be added to documentation)
	 **/
	public function deflate() {
		// Reset all attributes.
		$className = get_class($this);
		$freshObject = new $className();
		foreach ($freshObject as $key => $value) {
			$this->$key = $value;
		}
	}
	
	/**
	 * Inflate/Invigorate
	 *
	 * @return void
	 **/
	public function inflate($fieldsOrId = array(), $relatedEntities = array()) {
		if (is_array($fieldsOrId)) {
			if (!empty($fieldsOrId)) {
				$joins = array();
				foreach ($fieldsOrId as $fieldName => $fieldValue) {
					if (is_array($fieldValue)) {
						// field is data for a related object
						$this->inflateOject($fieldName, $fieldValue, $fieldsOrId);
					} else if (isset($this->fieldDefinitions[$fieldName])) {
						// field is part of this object's fields
						$this->fields[$fieldName] = $fieldValue;
					} else if (isset($this->derivedFieldDefinitions[$fieldName])) {
						// field is a derived field for this object
						$this->setDerivedField($fieldName, $fieldValue);
					} else if (($pos = strpos($fieldName, '.')) !== false) {
						// join data
						$joinObjectName = substr($fieldName, 0, $pos);
						$joinFieldName = substr($fieldName, $pos + 1);
						$joins[$joinObjectName][$joinFieldName] = $fieldValue;
					}
				}

				// At this point we are inflated / not new
				$this->isNew = false;

				// Construct objects using any join data passed in...
				foreach ($joins as $joinObjectName => $joinFields) {
					$this->inflateObject($joinObjectName, $joinFields, $joins);
				}
			}
		} else if ($fieldsOrId != '') {
			// an id was given
			foreach ($this->getPkFieldNames() as $fieldName) {
				$this->fields[$fieldName] = $fieldsOrId;
			}
			// TODO: Tom: To load or not to load automatically? I can give use cases on why someone wouldn't want it to autoload; plus the user should be using Object::constructByKey($key) anyway when they want it to pull from storage.
			
			// For others who do not understand the above:
			// There are, at the moment, two ways to construct an object with a key:
			// $customer = new Customer($customerId);
			// $customer = Customer::constructByKey($customerId);
			// The latter will always pull from the database and will return an object if the customer_id was found, or null if not found.
			// The former will also pull from the database if the following line is left in (it's bad practice, but convenient):
			$this->load();
		}
		
		// Set related entities that were passed in
		foreach ($relatedEntities as $name => $value) {
			$setMethod = 'set' . $name;
			$this->$setMethod($value);
		}
		
	}
	
	protected function inflateObject($objectName, $objectData, &$additionalData = array()) {
		if (isset($this->objectDefinitions[$objectName])) {
			// append to the object data any additional object data that isn't part of this object (read: chain inflation).
			foreach ($additionalData as $objectName2 => $objectData2) {
				if (is_array($objectData2) && !isset($this->objectDefinitions[$objectName2])) {
					$objectData[$objectName2] = $objectData2;
				}
			}
			// set the related object
			$setMethod = 'set' . $objectName;
			$this->$setMethod(call_user_func(array($this->objectDefinitions[$objectName]['class_name'], 'constructByFields'), $objectData));
		}
		
	}
}

?>