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
	 * Stores wether or not a check returned a row from the database.
	 *
	 * @var boolean
	 * @see didCheckReturnResult()
	 **/
	protected $checkReturnedResult = null;

	/**
	 * An array of all the columns in the database, including the primary key
	 * column and name columns.
	 *
	 * Format of "field_name" => attributes
	 *
	 * @var array
	 * @deprecated in core cough, use the $fields array to specified which fields are allowed (in addition to their default values)
	 **/
	// protected $fieldDefinitions = array();

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
	 * @see getDerivedField(), setDerivedField()
	 **/
	protected $derivedFields = array();

	/**
	 * The primary key field names
	 *
	 * Override in sub class.
	 * 
	 * @var array
	 * @see getPkFieldNames(), getPk()
	 **/
	protected $pkFieldNames = array();

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
	 * An array of all the collections and their attributes.
	 *
	 * The information is used by CoughObject to write SQL queries,
	 * among other things.
	 *
	 * Format of "collection_name" => array of attributes
	 *
	 * @todo Document that array of attributes. For now just look at the
	 * woc_Product_Generated class (at the defineCollections() function).
	 *
	 * @var array
	 **/
	protected $collectionDefinitions = array();
	
	/**
	 * An array of all the checked collections in form [collectionName] => [CoughCollection]
	 * 
	 * @var array
	 * @see getCollection(), checkCollection(), saveCheckedCollections()
	 **/
	protected $collections = array();
	
	/**
	 * An array of all the currently checked/populated collections.
	 *
	 * A collection that has not populated will not be be in the array at all.
	 *
	 * Format of "collection_name" => true
	 *
	 * @var string
	 * @see isCollectionChecked()
	 **/
	protected $checkedCollections = array();

	/**
	 * An array of all the objects and their attributes.
	 *
	 * The information is used by CoughObject to instantiate and check the
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
	 * An array of all the checked objects in form [objectName] => [CoughObject]
	 * 
	 * @var array
	 * @see getObject(), checkObject(), saveCheckedObjects(), isObjectChecked()
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
	 * Flag indicating whether or not the key ID was set as a preknown key, i.e.
	 * an insert should take place when saving.
	 *
	 * @var boolean
	 * @see setPreknownKeyId()
	 **/
	protected $isPreknownKeyIdSet = false;

	/**
	 * Stores any custom join fields if passed into the constructor.
	 *
	 * @var string
	 **/
	protected $joinTableName = null;
	protected $joinFields = array();
	protected $modifiedJoinFields = array();
	protected $isJoinTableModified = false;
	protected $isJoinTableNew = false;
	protected $collector = null;

	// ----------------------------------------------------------------------------------------------
	// CONSTRUCTORS and INITIALIZATION METHODS block BEGINS
	// ----------------------------------------------------------------------------------------------

	/**
	 * Construct an empty CoughObject or an object with pre-initialized data.
	 * 
	 * @param $fieldsOrID - initializes the object with the fields or id (does not query the database)
	 * @return void
	 **/
	public function __construct($fieldsOrID = array()) {
		// before chaining to construct, make sure you override initializeDefinitions() within the subclass
		//	and then invoke initializeDefinitions() in the constructor method.
		$this->initializeDefinitions();

		// Get our reference to the database object
		$this->db = DatabaseFactory::getDatabase($this->dbName);
		$this->db->selectDb($this->dbName);
		
		if (is_array($fieldsOrID)) {
			foreach ($fieldsOrID as $fieldName => $fieldValue) {
				// This next check has to either be array_key_exists($fieldName, $this->fields)
				// or isset($this->fieldDefinitions[$fieldName])...
				if (array_key_exists($fieldName, $this->fields)) {
					$this->fields[$fieldName] = $fieldValue;
				} else if (($pos = strpos($fieldName, '.')) !== false) {
					// custom field
					// $joinTableName = substr($fieldName, 0, $pos);
					$joinFieldName = substr($fieldName, $pos + 1);
					$this->joinFields[$joinFieldName] = $fieldValue;
				} else {
					$this->setDerivedField($fieldName, $fieldValue);
				}
			}
		} else if ($fieldsOrID != '') {
			foreach ($this->getPkFieldNames() as $fieldName) {
				$this->fields[$fieldName] = $fieldsOrID;
			}
		}
		
		$this->finishConstruction();
	}
	
	/**
	 * Called at the end of __construct(). Override for special construction
	 * behavior that is dependent on the object's state.
	 * 
	 * @return void
	 **/
	protected function finishConstruction() {
	}
	
	/**
	 * Sets the cough object's basic identity:
	 * 
	 *    - {@link $objectDefinitions}
	 *    - {@link $collectionDefinitions}
	 *    - default values
	 *
	 * @return void
	 **/
	protected function initializeDefinitions() {
		$this->defineObjects();
		$this->defineCollections();
	}
	
	protected function defineObjects() {
		// override this in subclass if the subclass possesses objects
	}
	
	protected function defineCollections() {
		// override this in subclass if the subclass possesses collections
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
		$clone = new $className();
		foreach ($clone as $key => $value) {
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
	 * TODO: If PHP ever provides a `operator==` overload function, have it
	 * call this as well so we can do $object1 == $object2 and have it work.
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
	 * Set a reference to the parent object.
	 * 
	 * Example:
	 * 
	 *     $order = new Order();
	 *     $orderLine = new OrderLine();
	 *     $orderLine->setCollector($order);
	 * 
	 * @param CoughObject $collector - the parent object
	 * @return void
	 * @author Anthony Bush
	 **/
	public function setCollector($collector) {
		$this->collector = $collector;
	}
	
	/**
	 * Determine whether or not a collector has been set.
	 *
	 * @return boolean
	 * @author Anthony Bush
	 **/
	public function hasCollector() {
		if ($this->collector instanceof CoughObject) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Get the parent object
	 *
	 * @return CoughObject - the parent object
	 * @author Anthony Bush
	 **/
	public function getCollector() {
		return $this->collector;
	}
	
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
		if (is_array($id)) {
			$this->setFields($id);
		} else {
			foreach ($this->getPkFieldNames() as $fieldName) {
				$this->setField($fieldName, $id);
			}
		}
	}

	/**
	 * Allows you to insert a record when you already know the primary key id.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function setPreknownKeyId($id) {
		$this->setKeyId($id);
		$this->isPreknownKeyIdSet = true;
	}
	
	/**
	 * Returns the current value of the object's primary key id.
	 * 
	 * If the key is multi-key, then it returns the same thing as {@link getPk()}
	 *
	 * @return mixed
	 * @author Anthony Bush
	 **/
	public function getKeyId() {
		if ($this->hasKeyId()) {
			// Implode to support multi-key PK in the KEYED collection (can't hash an array)
			return implode(',', $this->getPk());
		} else {
			return null;
		}
		// if (count($this->pkFieldNames) == 1) {
		// 	$fieldName = $this->pkFieldNames[0];
		// 	if (isset($this->fields[$fieldName])) {
		// 		return $this->fields[$fieldName];
		// 	} else {
		// 		return null;
		// 	}
		// } else {
		// 	return $this->getPk();
		// }
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
	public function getField($fieldName) {
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
	public function setField($fieldName, $value) {
		$this->fields[$fieldName] = $value;
		$this->setModifiedField($fieldName);
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
		foreach ( $fields as $fieldName => $fieldValue ) {
			if (array_key_exists($fieldName, $this->fields)) {
				$this->setField($fieldName, $fieldValue);
			} else if (($pos = strpos($fieldName, '.')) !== false) {
				// custom field
				// $joinTableName = substr($fieldName, 0, $pos);
				$joinFieldName = substr($fieldName, $pos + 1);
				$this->setJoinField($joinFieldName, $fieldValue);
			} else {
				$this->setDerivedField($fieldName, $fieldValue);
			}
		}
	}
	
	/**
	 * Sets a read-only field; It's usually a derived field from a complex
	 * SQL query such as when overriding the getCheckSql() function.
	 *
	 * @param string $fieldName - the derived field name to set
	 * @param mixed $fieldValue - the value to store
	 * @return void
	 * @see getCheckSql()
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
	 * @see setDerivedField(), getCheckSql()
	 * @author Anthony Bush
	 **/
	public function getDerivedField($fieldName) {
		if (isset($this->derivedFields[$fieldName])) {
			return $this->derivedFields[$fieldName];
		} else {
			return null;
		}
	}
	
	public function setJoinTableName($joinTableName) {
		$this->joinTableName = $joinTableName;
	}

	public function getJoinTableName() {
		return $this->joinTableName;
	}

	public function setIsJoinTableNew($isJoinTableNew) {
		$this->isJoinTableNew = $isJoinTableNew;
	}

	public function getJoinFields() {
		return $this->joinFields;
	}
	
	public function setJoinFields($fieldValues) {
		if (is_array($fieldValues)) {
			foreach ($fieldValues as $fieldName => $fieldValue) {
				$this->setJoinField($fieldName, $fieldValue);
			}
		}
	}
	
	public function getJoinField($fieldName) {
		if (array_key_exists($fieldName, $this->joinFields)) {
			return $this->joinFields[$fieldName];
		}
		return null;
	}
	
	public function setJoinField($fieldName, $fieldValue) {
		if (array_key_exists($fieldName, $this->joinFields)) {
			if ($this->joinFields[$fieldName] !== $fieldValue) {
				$this->modifiedJoinFields[$fieldName] = $this->joinFields[$fieldName];
				$this->isJoinTableModified = true;
				$this->joinFields[$fieldName] = $fieldValue;
			}
		} else {
			$this->isJoinTableModified = true;
			$this->joinFields[$fieldName] = $fieldValue;
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
	 * @return boolean - whether or not check was able to find a record in the database.
	 * @author Anthony Bush
	 **/
	public function check() {
		return $this->checkBySql($this->getCheckSql());
	}
	
	/**
	 * Returns the current SQL statement that the {@link check()} method should
	 * run.
	 * 
	 * Override this in sub classes for custom SQL.
	 *
	 * @return mixed - string of SQL or empty string if no SQL to run.
	 * @author Anthony Bush
	 **/
	protected function getCheckSql() {
		if ($this->hasKeyId()) {
			$sql = 'SELECT * FROM ' . $this->dbName . '.' . $this->tableName . ' ' . $this->db->generateWhere($this->getPk());
		} else {
			$sql = '';
		}
		return $sql;
	}
	
	/**
	 * Provides a way to `check` by an array of "key" => "value" pairs.
	 *
	 * @param array $where - an array of "key" => "value" pairs to search for
	 * @param boolean $additionalSql - add ORDER BYs and LIMITs here.
	 * @return boolean - true if initialized object with data, false otherwise.
	 * @author Anthony Bush
	 **/
	public function checkByCriteria($where = array(), $additionalSql = '') {
		if ( ! empty($where)) {
			$sql = 'SELECT * FROM ' . $this->dbName . '.' . $this->tableName
			     . ' ' . $this->db->generateWhere($where) . ' ' . $additionalSql;
			return $this->checkBySql($sql);
		}
		return false;
	}
	
	/**
	 * Provides a way to `check` by custom SQL.
	 *
	 * @param string $sql - custom SQL to use during the check
	 * @param boolean $allowManyRows - set to true if you want to initialize from a record even if there was more than one record returned.
	 * @return boolean - true if initialized object with data, false otherwise.
	 * @author Anthony Bush
	 **/
	public function checkBySql($sql) {
		if ( ! empty($sql)) {
			$this->db->selectDb($this->dbName);
			$result = $this->db->query($sql);
			if ($result->numRows() == 1) {
				$this->initFields($result->getRow());
				$this->setCheckReturnedResult(true);
			} else {
				// check failed because the unique dataset couldn't be selected
				$this->setCheckReturnedResult(false);
			}
			$result->freeResult();
		} else {
			$this->setCheckReturnedResult(false);
		}
		return $this->didCheckReturnResult();
	}

	/**
	 * Set whether or not a check returned a result from the database.
	 *
	 * @param boolean $value - true if check returned a result, false if not.
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function setCheckReturnedResult($value) {
		$this->checkReturnedResult = $value;
	}

	/**
	 * Get whether or not a check returned a result from the database.
	 *
	 * Note that there is no `getCheckReturnedResult` function, as this is it.
	 *
	 * @return boolean - true if check returned a result, false if not.
	 * @author Anthony Bush
	 **/
	public function didCheckReturnResult() {
		return $this->checkReturnedResult;
	}

	/**
	 * Clear the list of modified fields and other modified flags.
	 *
	 * @return void
	 **/
	protected function resetModified() {
		$this->modifiedFields = array();
		$this->isPreknownKeyIdSet = false;
		$this->modifiedJoinFields = array();
		$this->isJoinTableModified = false;
		$this->isJoinTableNew = false;
	}

	/**
	 * Add a field to the list of modified fields
	 *
	 * @param string $fieldName
	 * @return void
	 **/
	protected function setModifiedField($fieldName) {
		$this->modifiedFields[$fieldName] = true;
	}

	/**
	 * Get a list of all of this object's modified values.
	 *
	 * @return associative array - all modified values in [field_name] => [field_value] form
	 **/
	protected function getModifiedFields() {
		$fields = array();
		foreach ($this->modifiedFields as $fieldName => $isModified) {
			if ($isModified) {
				$fields[$fieldName] = $this->getField($fieldName);
			}
		}
		return $fields;
	}
	
	/**
	 * If the object has a parent, then this method will update its foreign
	 * key with the value from the parent's primary key.
	 * 
	 * It's called automatically by {@link save()}.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function setFieldsFromParentPk() {
		if ($this->hasCollector()) {
			$collector = $this->getCollector();
			foreach ($this->objectDefinitions as $objProperties) {
				if ($collector instanceof $objProperties['class_name']) {
					$getter = $objProperties['get_id_method']; // TODO: Make multi-key PK compliant
					if ($this->$getter() === null) {
						$this->setFields($collector->getPk());
					}
					break;
				}
			}
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
		
		// Update the child with it's parent id
		$this->setFieldsFromParentPk();
		
		// Check for valid data.
		$this->validateData($this->fields);
		if ( ! $this->isDataValid()) {
			return false;
		}
		
		// Save self first, in case the PK is needed for remaining saves.
		if ($this->shouldInsert()) {
			$result = $this->insert();
		} else {
			$result = $this->update();
		}

		$this->saveCheckedCollections();
		
		$this->saveJoinFields();
		
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
		if (!$this->hasKeyId() || $this->isPreknownKeyIdSet === true) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Saves all the checked objects (if it wasn't checked there would be
	 * nothing to save)
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function saveCheckedObjects() {
		foreach ($this->objects as $object) {
			$object->save();
		}
	}
	
	/**
	 * Saves all the checked collections (if it wasn't checked there would be
	 * nothing to save)
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function saveCheckedCollections() {
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
	public function insert() {
		
		$fields = $this->getInsertFields();
		
		$this->db->selectDb($this->dbName);
		if (!$this->hasKeyId()) {
			$id = $this->db->insert($this->tableName, $fields);
		} else {
			$this->db->insertOrUpdate($this->tableName, $fields, null, $this->getPk());
			$id = null;
		}
		if ($id != '') {
			$this->setKeyId($id); // TODO: What is $id set to when a multi-pk exists? as long as it's null or empty, we are okay.
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
		return $this->getModifiedFields();
	}
	
	/**
	 * Updates the database with modified values, if any.
	 *
	 * @return boolean - true
	 **/
	public function update() {
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
		return $this->getModifiedFields();
	}
	
	/**
	 * Deletes the record from the database, if hasKeyId returns true.
	 *
	 * @return boolean - whether or not the delete was executed.
	 * @author Anthony Bush
	 **/
	public function delete() {
		if ($this->hasKeyId()) {
			$this->db->delete($this->tableName, $this->getPk());
			return true;
		} else {
			return false;
		}
	}
	
	// ----------------------------------------------------------------------------------------------
	// object database methods / collection handling methods block ENDS
	// ----------------------------------------------------------------------------------------------

	/**
	 * Checks the object using the configuration in `objectDefinitions`.
	 * 
	 * Generic (aka generated) check methods, e.g. `checkProduct_Object`,
	 * should call this method, e.g. `$this->_checkObject('product');`
	 * 
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function _checkObject($objectName) {
		$objectInfo = &$this->objectDefinitions[$objectName];
		$object = new $objectInfo['class_name']($this->$objectInfo['get_id_method']());
		$object->check();
		$this->objects[$objectName] = $object;
	}
	
	/**
	 * Calls the check method for the given object name.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function checkObject($objectName) {
		$checkMethod = 'check' . ucfirst($collectionName) . '_Object';
		$this->$checkMethod();
	}
	
	/**
	 * Tells whether or not the given object name has been checked.
	 *
	 * @return boolean
	 * @author Anthony Bush
	 * @todo Determine whether we need to switch to array_key_exists here. Depends on whether we set the value to null (in which case yes) or an empty object (in which case no) when the object is checked, but not found in the database.
	 **/
	protected function isObjectChecked($objectName) {
		return isset($this->objects[$objectName]);
	}
	
	/**
	 * Returns the specified object for use.
	 *
	 * @param $objectName - the name of the object to get
	 * @return CoughObject - the requested object
	 * @author Anthony Bush
	 **/
	protected function getObject($objectName) {
		if ( ! $this->isObjectChecked($objectName)) {
			$this->checkObject($objectName);
		}
		return $this->objects[$objectName];
	}
	
	/**
	 * Sets the object reference in memory.
	 * 
	 * This has no effect on the database. For example:
	 * 
	 *     $order->setCustomer($customer);
	 * 
	 * will not change the customer_id on the order. It is simply a way to pass
	 * in pre-instantiated objects so that they do not have to be looked up in
	 * the database.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function setObject($objectName, $object) {
		if (isset($this->objectDefinitions[$objectName])) {
			$this->objects[$objectName] = $object;
		}
	}
	
	/**
	 * Checks the collection using the configuration in `collectionDefinitions`.
	 * 
	 * You can override the SQL, element name, and orderBySQL options.
	 * 
	 * @todo Document parameters
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function _checkCollection($collectionName, $elementName = '', $sql = '', $orderBySQL = '') {
		$def =& $this->collectionDefinitions[$collectionName];
		
		$collection = new $def['collection_class']();
		
		if (isset($def['join_table'])) {
			$collection->setCollector($this, CoughCollection::MANY_TO_MANY, $def['join_table']);
			
			if (empty($sql) && $this->hasKeyId()) {
				$sql = '
					SELECT ' . $def['collection_table'] . '.*' . $this->getJoinSelectSql($collectionName) . '
					FROM ' .$def['join_table'] . '
					INNER JOIN ' . $def['collection_table'] . ' ON ' . $def['join_table'] . '.' . $def['collection_key']
						. ' = ' . $def['collection_table'] . '.' . $def['collection_key'] . '
					WHERE ' . $def['join_table'] . '.' . $def['relation_key'] . ' = ' . $this->db->quote($this->getKeyId());

				if (isset($def['retired_column']) && ! empty($def['retired_column'])) {
					$sql .= '
						AND ' . $def['collection_table'] . '.' . $def['retired_column'] . ' = ' . $this->db->quote($def['is_not_retired']);
				}

				if (isset($def['join_table_attr'])) {
					$joinAttr =& $def['join_table_attr'];
					if (isset($joinAttr['retired_column']) && ! empty($joinAttr['retired_column'])) {
						$sql .= '
							AND ' . $def['join_table'] . '.' . $joinAttr['retired_column'] . ' = ' . $this->db->quote($joinAttr['is_not_retired']);
					}
				}
			}
			
		} else {
			$collection->setCollector($this, CoughCollection::ONE_TO_MANY);
			
			if (empty($sql) && $this->hasKeyId()) {
				$sql = '
					SELECT *
					FROM ' . $def['collection_table'] . '
					WHERE ' . $def['relation_key'] . ' = ' . $this->db->quote($this->getKeyId());

				if (isset($def['retired_column']) && ! empty($def['retired_column'])) {
					$sql .= '
						AND ' . $def['retired_column'] . ' = ' . $this->db->quote($def['is_not_retired']);
				}
			}
			
		}
		
		if (empty($elementName)) {
			$elementName = $def['element_class'];
		}
		
		$collection->populateCollection($elementName, $sql, $orderBySQL);
		
		$this->collections[$collectionName] = $collection;
	}
	
	/**
	 * Calls the check method for the given collection name.
	 *
	 * @param string $collectionName - the name of the collection to check
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function checkCollection($collectionName) {
		$checkMethod = 'check' . ucfirst($collectionName) . '_Collection';
		$this->$checkMethod();
	}
	
	/**
	 * Returns a string of all the join fields for the specified collection.
	 * 
	 * Ideally, this will just access a generated attribute, but for now
	 * it does a DESCRIBE on the join table.
	 * 
	 * TODO: CoughGenerator: Generate an extra attribute 'join_table_fields'
	 * that is an array of the fields on the join table.
	 * 
	 * TODO: Cough: Update this function to use the above when it is present.
	 * If it is not present, continue to do the DESCRIBE.
	 * 
	 * @param string $collectionName - the collection name for which to get the join fields
	 * @return string
	 * @author Anthony Bush
	 **/
	protected function getJoinSelectSql($collectionName) {
		$def =& $this->collectionDefinitions[$collectionName];
		
		// Get extra join fields on-the-fly
		$joinFields = array();
		$sql = 'DESCRIBE '. $this->dbName . '.' . $def['join_table'];
		$result = $this->db->query($sql);
		while ($row = $result->getRow()) {
			$joinFieldName = $row['Field'];
			$joinFields[] = $def['join_table'] . '.' . $joinFieldName . ' AS `' . $def['join_table'] . '.' . $joinFieldName . '`';
		}
		$result->freeResult();
		if ( ! empty($joinFields)) {
			$joinFieldSql = ',' . implode(',', $joinFields);
		} else {
			$joinFieldSql = '';
		}
		return $joinFieldSql;
	}

	/**
	 * Tells whether or not the given collection name has been checked.
	 *
	 * @return boolean
	 * @author Anthony Bush
	 **/
	protected function isCollectionChecked($collectionName) {
		return isset($this->collections[$collectionName]);
	}
	
	/**
	 * Returns the specified collection for use.
	 *
	 * @param $collectionName - the name of the collection to get
	 * @return CoughCollection - the requested collection object
	 * @author Anthony Bush
	 **/
	protected function getCollection($collectionName) {
		if ( ! $this->isCollectionChecked($collectionName)) {
			$this->checkCollection($collectionName);
		}
		return $this->collections[$collectionName];
	}
	
	protected function saveJoinFields() {
		
		if ($this->isJoinTableNew) {

			$this->setJoinFields($this->getPk());
			$this->setJoinFields($this->getCollector()->getPk());
			$this->db->insertOnDupUpdate($this->getJoinTableName(),$this->getJoinFields());
			
		} else if ($this->isJoinTableModified) {

			$this->setJoinFields($this->getPk());
			$this->setJoinFields($this->getCollector()->getPk());
			$fieldsToSave = array();
			$whereFields = array();
	
			foreach ($this->getJoinFields() as $fieldName => $fieldValue) {
				if (array_key_exists($fieldName, $this->modifiedJoinFields)) {
					$whereFields[$fieldName] = $this->modifiedJoinFields[$fieldName];
					$fieldsToSave[$fieldName] = $fieldValue;
				} else {
					$whereFields[$fieldName] = $fieldValue;
				}
			}
			$this->db->insertOrUpdate($this->getJoinTableName(),$fieldsToSave,null,$whereFields);
		}
	}
	
	/**
	 * Validates data stored in the model. It is called automatically by `save`,
	 * which will return false if this function sets any errors.
	 * 
	 * @param array $data - the data  to validate in [field_name] => [value] form.
	 * @return void
	 * @see isDataValid(), getValidationErrors()
	 * @author Anthony Bush
	 **/
	public function validateData(&$data) {
		if (!$this->validatedData) {
			$this->doValidateData($data);
		}
		$this->validatedData = true;
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
	 * @param string $fieldName - the field name to check.
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
	
	/**
	 * PHP Magic method to allow automatic getter -> getField / getDerivedField / getJoinField.
	 * 
	 * If no method can be called, it generates the same Fatal Error PHP does
	 * when trying to call a non-existent method name.
	 * 
	 * @param string $method - the method name invoked by the caller.
	 * @param array $args - the method arguments given by the caller.
	 * @return mixed - return value of method user is calling.
	 * @author Anthony Bush
	 * @since 2007-04-13
	 * @see http://us.php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.methods
	 **/
	public function __call($method, $args) {
		$underscorePos = strrpos($method, '_');
		
		if ($underscorePos === false) {
			// Not a getJoin_, get*_Collection, or get*_Object call, so must be a field call.
			
			// Allow: getFieldName, which will invoke getField('field_name').
			if (strpos($method, 'get') === 0) {
				return $this->getField($this->_underscore(substr($method, 3)));
			}
			// Allow: setFieldName, which will invoke setField('field_name').
			else if (strpos($method, 'set') === 0) {
				$field = $this->_underscore(substr($method, 3));
				if (isset($args[0])) {
					$value = $args[0];
				} else {
					$value = null;
				}
				return $this->setField($field, $value);
			}
			// Allow: addObjectName($object, $joinFields = null),
			// which will invoke getCollection('object_name')->add($object, $joinFields);
			else if (strpos($method, 'add') === 0) {
				$objectName = $this->_underscore(substr($method, 3));
				if (isset($this->collectionDefinitions[$objectName])) {
					if (isset($args[0])) {
						$objectOrId = $args[0];
						if (isset($args[1])) {
							$joinFields = $args[1];
						} else {
							$joinFields = null;
						}
						return $this->getCollection($objectName)->add($objectOrId, $joinFields);
					}
					return false;
				}
			}
			// Allow: removeObjectName($object),
			// which will invoke getCollection('object_name')->remove($object);
			else if (strpos($method, 'remove') === 0) {
				$objectName = $this->_underscore(substr($method, 6));
				if (isset($this->collectionDefinitions[$objectName])) {
					if (isset($args[0])) {
						$objectOrId = $args[0];
						$removedObject = $this->getCollection($objectName)->remove($objectOrId);
						$def =& $this->collectionDefinitions[$objectName];
						if (isset($def['join_table_attr'])) {
							// Retire the join
							$removedObject->setJoinField($def['join_table_attr']['retired_column'], $def['join_table_attr']['is_retired']);
						} else {
							// Null out the foreign key
							$removedObject->setField($def['relation_key'], null);
						}
						return true;
					}
					return false;
				}
			}
			
		} else {
			if ((substr($method, $underscorePos + 1) === 'Object')) {
				if (strpos($method, 'get') === 0) {
					$objectName = substr($method, 3, $underscorePos - 3);
					return $this->getObject($objectName);
				} else if (strpos($method, 'set') === 0) {
					$objectName = substr($method, 3, $underscorePos - 3);
					if (isset($args[0])) {
						$value = $args[0];
					} else {
						$value = null;
					}
					return $this->setObject($objectName, $value);
				} else if (strpos($method, 'check') === 0) {
					$objectName = substr($method, 5, $underscorePos - 5);
					return $this->_checkObject($objectName);
				}

			}
			else if ((substr($method, $underscorePos + 1) === 'Collection')) {
				if (strpos($method, 'get') === 0) {
					$tables = explode('_', substr($method, 3, $underscorePos - 3));
					if (count($tables) == 1) {
						return $this->getCollection($tables[0]);
					} else {

					}
				} else if (strpos($method, 'check') === 0) {
					$collectionName = substr($method, 5, $underscorePos - 5);
					return $this->_checkCollection($collectionName);
				}
			}
			
			// CONSIDER: getJoinName_Join() because (1) makes naming consistent for users AND less work for this magic method to do.
			// Allow: getJoin_Customer_RelationshipStartDate calls, which will invoke getJoinField('relationship_start_date') for now.
			else if (strpos($method, 'getJoin_') === 0)
			{
				$methodPieces = explode('_', $method);
				if (count($methodPieces) == 3) {
					$field = $this->_underscore($methodPieces[2]);
					return $this->getJoinField($field);
				}
			}
			// Allow: setJoin_Customer_RelationshipStartDate calls, which will invoke setJoinField('relationship_start_date', $value) for now.
			else if (strpos($method, 'setJoin_') === 0)
			{
				$methodPieces = explode('_', $method);
				if (count($methodPieces) == 3) {
					$field = $this->_underscore($methodPieces[2]);
					if (isset($args[0])) {
						$value = $args[0];
					} else {
						$value = null;
					}
					return $this->setJoinField($field, $value);
				}
			}
		}
		
		
		// Don't break useful errors
		$errorMsg = 'Call to undefined method ' . __CLASS__ . '::' . $method . '()';
		$bt = debug_backtrace();
		if (count($bt) > 1) {
			$errorMsg .= ' in <b>' . @$bt[1]['file'] . '</b> on line <b>' . @$bt[1]['line'] . '</b>';
		}
		$errorMsg .= '. CoughObject\'s magic method was invoked';
		trigger_error($errorMsg, E_USER_ERROR);
	}
	
	protected function _titleCase($underscoredString) {
		return str_replace(' ', '', str_replace('_', ' ', ucwords($underscoredString)));
	}
	
	protected function _underscore($camelCasedString) {
		return preg_replace('/_i_d$/', '_id', strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $camelCasedString)));
	}
	
}


?>