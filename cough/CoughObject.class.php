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
 * @package cough
 **/
abstract class CoughObject {
	
	/**
	 * An array of all the currently initialized or set fields.
	 *
	 * Format of "field_name" => value
	 *
	 * @var array
	 * @see getField(), getFields(), getFieldsWithoutPk(), setField(), setFields()
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
	 * An array of all the loaded objects in form [objectName] => [CoughObject]
	 * 
	 * @var array
	 * @see getObject(), loadObject(), saveLoadedObjects(), isObjectLoaded()
	 **/
	protected $objects = array();
	
	/**
	 * An array of all the loaded collections in form [collectionName] => [CoughCollection]
	 * 
	 * @var array
	 * @see getCollection(), loadCollection(), saveLoadedCollections(), isCollectionLoaded()
	 **/
	protected $collections = array();

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

	/**
	 * An array of all the columns in the database, including the primary key
	 * column and name columns.
	 *
	 * Format of "field_name" => attributes
	 *
	 * @var array
	 * @see defineFields()
	 **/
	protected $fieldDefinitions = array();
	
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
	 * one of the generated class's defineObjects().
	 *
	 * @var array
	 **/
	protected $objectDefinitions = array();

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
		$this->initializeDefinitions();
		
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
		$this->defineFields();
		$this->defineDerivedFields();
		$this->defineObjects();
	}

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
	 * Constructs a new object from a single id (for single key PKs) or a hash of
	 * [field_name] => [field_value].
	 * 
	 * The key is used to pull data from the database, and, if no data is found,
	 * null is returned. You can use this function with any unique keys or the
	 * primary key as long as a hash is used. If the primary key is a single
	 * field, you may pass its value in directly without using a hash.
	 * 
	 * @param mixed $idOrHash - id or hash of [field_name] => [field_value]
	 * @return mixed - CoughObject or null if no record found.
	 * @todo PHP 5.3: switch from call_user_func to static::methodName() and remove the $className parameter
	 **/
	public static function constructByKey($idOrHash, $className = '') {
		if (is_array($idOrHash)) {
			$fields = $idOrHash;
		} else {
			$fields = array();
			$tableName = call_user_func(array($className, 'getTableName'));
			foreach (call_user_func(array($className, 'getPkFieldNames')) as $fieldName) {
				$fields[$tableName . '.' . $fieldName] = $idOrHash;
			}
		}
		
		if (!empty($fields)) {
			$db = call_user_func(array($className, 'getDb'));
			$sql = call_user_func(array($className, 'getLoadSql'));
			if (is_object($sql)) {
				$sql->addWhere($fields);
				$sql = $sql->getString();
			} else {
				$query = new As_Query($db);
				$sql .= ' WHERE ' . $query->buildWhereSql($fields);
			}
			return call_user_func(array($className, 'constructBySql'), $sql);
		}
		return null;
	}
	
	/**
	 * Constructs a new object from custom SQL.
	 *
	 * @param string $sql
	 * @return mixed - CoughObject if exactly one row found, null otherwise.
	 * @todo PHP 5.3: switch from call_user_func to static::methodName() and remove the $className parameter
	 **/
	public static function constructBySql($sql, $className = '') {
		if (!empty($sql)) {
			$db = call_user_func(array($className, 'getDb'));
			$dbName = call_user_func(array($className, 'getDbName'));
			$db->selectDb($dbName);
			$result = $db->query($sql);
			if ($result->getNumRows() == 1) {
				return call_user_func(array($className, 'constructByFields'), $result->getRow());
			} else {
				// load failed because the unique dataset couldn't be selected
			}
		} else {
			// load failed because no SQL was given
		}
		return null;
	}
	
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
		$this->setKeyId(null, false);
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
	 * @param boolean $notifyChildren whether or not to notify children (collections) of the key change.  Think of it as a cascade update of the FKs.
	 * @return void
	 * @author Anthony Bush
	 **/
	public function setKeyId($id, $notifyChildren = true) {
		if (!is_array($id)) {
			$key = array();
			foreach ($this->getPkFieldNames() as $fieldName) {
				$key[$fieldName] = $id;
			}
		} else {
			$key = $id;
		}
		if ($notifyChildren) {
			$this->notifyChildrenOfKeyChange($key);
		}
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
	 * Returns whether or not the object is inflated (i.e. pulled from persistent
	 * storage)
	 * 
	 * This method returns the opposite of {@link isNew()}
	 * 
	 * The reason this method isn't called `isLoaded()` is because it's possible for
	 * an object to become inflated from data that was not loaded from a database /
	 * persistent storage.  It's meant to answer the question, "Can I call the
	 * getters on this object and expect to get meaningful values?"
	 * 
	 * @return boolean
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
	 * @return mixed - string for multi-key PKs, integer/string for single-key PKs
	 * @author Anthony Bush
	 **/
	public function getKeyId() {
		$pkFieldNames = $this->getPkFieldNames();
		if (count($pkFieldNames) === 1) {
			return $this->fields[$pkFieldNames[0]];
		} else {
			$keyId = '';
			foreach ($pkFieldNames as $fieldName) {
				$keyId .= $this->fields[$fieldName] . ',';
			}
			return substr_replace($keyId, '', -1);
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
		foreach ($this->getPkFieldNames() as $fieldName) {
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
		
		$pkFieldNames = $this->getPkFieldNames();
		
		// Must have at least one field marked as PK.
		if (empty($pkFieldNames)) {
			return false;
		}
		
		// All PK fields must be initialized
		foreach ($pkFieldNames as $fieldName) {
			if (!isset($this->fields[$fieldName])) {
				return false;
			}
		}
		
		// If it's a new object, then all the key fields must have been modified
		if ($this->isNew()) {
			$numModified = 0;
			foreach ($pkFieldNames as $fieldName) {
				if ($this->isFieldModified($fieldName)) {
					++$numModified;
				}
			}
			if ($numModified != count($pkFieldNames)) {
				return false;
			}
		}
		
		// Search exhausted: unable to disprove key's existance
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
	 * Gets fields by going through their getter methods, so that polymorphism is not
	 * broken (you can override getFieldName(), and that is the value you'll get).
	 * 
	 * @return array $fields - format of [field_name] => [new_value]
	 * @since 2008-12-18
	 **/
	public function getFieldsThroughGetters()
	{
		$fields = array();
		foreach ($this->fieldDefinitions as $fieldName => $fieldAttr) {
			$getter = 'get' . self::titleCase($fieldName);
			$fields[$fieldName] = $this->$getter();
		}
		return $fields;
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
	 * Sets fields by going through their setter methods, so that polymorphism is not
	 * broken (you can override setFieldName($val), and that is the method that will
	 * be called).
	 * 
	 * @param array $fields - format of [field_name] => [new_value]
	 * @return void
	 * @since 2008-12-17
	 **/
	public function setFieldsThroughSetters($fields)
	{
		foreach ($fields as $fieldName => $fieldValue) {
			if (isset($this->fieldDefinitions[$fieldName])) {
				$setter = 'set' . self::titleCase($fieldName);
				$this->$setter($fieldValue);
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
				if (isset($this->fieldDefinitions[$fieldName])) {
					$this->setField($fieldName, $fieldValue);
				} else if (isset($this->derivedFieldDefinitions[$fieldName])) {
					$this->setDerivedField($fieldName, $fieldValue);
				}
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
	 * Whether or not there is at least one modified field.
	 *
	 * @return boolean
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
	 * Whether or not the specified field has been modified.
	 *
	 * @return boolean
	 * @author Anthony Bush
	 * @since 2008-09-03
	 **/
	public function isFieldModified($fieldName) {
		// Implementation note: have to use the slower `array_key_exists` function
		// instead if `isset` b/c we store the original value which could be null and
		// causes `isset` to return false.
		return array_key_exists($fieldName, $this->modifiedFields);
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
		
		$db = $this->getDb();
		$db->selectDb($this->getDbName());
		$query = As_Query::getInsertQuery($db);
		$query->setTableName($this->getTableName());
		$query->setFields($fields);
		$numAffectedRows = $db->execute($query->getString());
		if ($numAffectedRows > 0) {
			if (!$this->hasKeyId()) {
				$this->setKeyId($db->getLastInsertId());
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
			$db = $this->getDb();
			$db->selectDb($this->getDbName());
			$query = As_Query::getUpdateQuery($db);
			$query->setTableName($this->getTableName());
			$query->setFields($fields);
			$query->setWhere($this->getPk());
			$db->execute($query->getString());
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
			$db = $this->getDb();
			$db->selectDb($this->getDbName());
			$query = new As_Query($db);
			$db->execute('DELETE FROM `' . $this->getTableName() . '` WHERE ' . $query->buildWhereSql($this->getPk()));
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
	 * Returns the validation errors set by `validateData()`.
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
	 * Unsets all keys on the given array that start with the specified prefix.
	 * 
	 * @param array $arr
	 * @param string $prefix
	 * @return void
	 * @author Anthony Bush, Richard Pistole
	 * @since 2008-02-20
	 **/
	public static function unsetKeysWithPrefix(&$arr, $prefix)
	{
		foreach (array_keys($arr) as $key)
		{
			if (strpos($key, $prefix) === 0)
			{
				unset($arr[$key]);
			}
		}
	}
	
	/**
	 * Returns a sub array of the given array containing all elements that have keys
	 * starting with the specified prefix.  The resulting array's keys have the
	 * prefix removed.
	 * 
	 * @param array $arr
	 * @param string $prefix
	 * @return array
	 * @author Anthony Bush, Richard Pistole
	 * @since 2008-02-20
	 **/
	public static function getKeysWithPrefix(&$arr, $prefix)
	{
		$subArr = array();
		$prefixLength = strlen($prefix);
		foreach ($arr as $key => $value)
		{
			if (strpos($key, $prefix) === 0)
			{
				$subArr[substr($key, $prefixLength)] = $value;
			}
		}
		return $subArr;
	}
	
	/**
	 * Returns a sub array of the given array containing all elements that have keys
	 * starting with the specified prefix.  The resulting array's keys have the
	 * prefix removed.
	 * 
	 * The array passed by reference has all the items that were added to the
	 * returned array removed.
	 * 
	 * @param array $arr
	 * @param string $prefix
	 * @return void
	 * @author Anthony Bush, Richard Pistole
	 * @since 2008-02-20
	 **/
	public static function splitArrayWithPrefix(&$arr, $prefix)
	{
		$subArr = array();
		$prefixLength = strlen($prefix);
		foreach (array_keys($arr) as $key)
		{
			if (strpos($key, $prefix) === 0)
			{
				$subArr[substr($key, $prefixLength)] = $arr[$key];
				unset($arr[$key]);
			}
		}
		return $subArr;
	}
	
	/**
	 * Helper method for generating SELECT criteria for other join tables.
	 * 
	 * For example, you might need the following SQL:
	 * 
	 *     <code>
	 *     $sql = '
	 *     SELECT
	 *         product.*
	 *         , `manufacturer`.`manufacturer_id` AS `Manufacturer_Object.manufacturer_id`
	 *         , `manufacturer`.`name` AS `Manufacturer_Object.name`
	 *         , `manufacturer`.`description` AS `Manufacturer_Object.description`
	 *         , `manufacturer`.`url` AS `Manufacturer_Object.url`
	 *     FROM
	 *         product
	 *         INNER JOIN manufacturer USING (manufacturer_id)
	 *     ';
	 *     </code>
	 * 
	 * But, rather than hand coding all the fields (which might change) on the
	 * manufacturer join, you can use getFieldAliases():
	 * 
	 *     <code>
	 *     $sql = '
	 *     SELECT
	 *         product.*
	 *         , ' . implode("\n\t, ", CoughObject::getFieldAliases('con_Manufacturer', 'Manufacturer_Object')) . '
	 *     FROM
	 *         product
	 *         INNER JOIN manufacturer USING (manufacturer_id)
	 *     ';
	 *     </code>
	 * 
	 * @param string $className class to get fields for, e.g. Address
	 * @param string $objectName object to alias fields to, e.g. BillingAddress_Object
	 * @param string $tableName optional table name; use if aliasing the table name (e.g. address to billing_address)
	 * @return array of field Aliases
	 * @author Anthony Bush, Richard Pistole
	 * @since 2008-02-21
	 **/
	public static function getFieldAliases($className, $objectName, $tableName = '')
	{
		$aliases = array();
		
		$object = new $className();
		if (empty($tableName)) {
			$tableName = call_user_func(array($className, 'getTableName'));
		}

		foreach(array_keys($object->getFields()) as $key)
		{
			$aliases[] =  '`' . $tableName. '`.`' . $key . '` AS `' . $objectName . '.' . $key . '`';
		}

		return $aliases;			
	}
	
	/**
	 * Builds a basic SELECT table.* FROM db.table query object (making it easy to
	 * inject joins, where criteria, order by, group by, etc.)
	 * 
	 * @return As_SelectQuery
	 * @author Anthony Bush
	 * @since 2008-08-26
	 * @todo PHP 5.3+: use "static" keyword instead of call_user_func and remove the
	 * parameter requirement (making sure that if called with it, no warnings/notices
	 * are generated; i.e. keep backward compatibility)
	 **/
	public static function buildSelectQuery($className)
	{
		$tableName = call_user_func(array($className, 'getTableName'));
		$query = new As_SelectQuery(call_user_func(array($className, 'getDb')));
		$query->setSelect('`' . $tableName . '`.*');
		$query->setFrom('`' . call_user_func(array($className, 'getDbName')) . '`.`' . $tableName . '`');
		return $query;
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
	 * Inflate/Invigorate the object with data.
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
						$this->inflateObject($fieldName, $fieldValue, $fieldsOrId);
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
		}
		
		// Set related entities that were passed in
		foreach ($relatedEntities as $name => $value) {
			$setMethod = 'set' . $name;
			$this->$setMethod($value);
		}
		
	}
	
	/**
	 * This helper method for {@link inflate()} handles inflation of related objects.
	 *
	 * @return void
	 **/
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
