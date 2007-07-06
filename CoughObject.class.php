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
	 * Format of "db_column_name" => "Error Text"
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
	 **/
	protected $checkReturnedResult = null;

	/**
	 * An array of all the columns in the database, including the primary key
	 * column and name columns.
	 *
	 * Format of "db_column_name" => "Display Name"
	 *
	 * @var array
	 **/
	protected $fieldDefinitions = array();

	/**
	 * An array of all the currently initialized or set fields.
	 *
	 * Format of "db_column_name" => value
	 *
	 * @var array
	 **/
	protected $fields = array();

	/**
	 * An array of fields that have been modified.
	 *
	 * @var array
	 **/
	protected $modifiedFields = array();
	
	/**
	 * An array of derived fields (read-only, as in not saved back to the
	 * database).
	 * 
	 * Format of "derived_field_name" => value
	 *
	 * @var array
	 **/
	protected $derivedFields = array();

	/**
	 * The primary key field names
	 *
	 * Override in sub class.
	 * 
	 * @var array
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
	 * The SQL statement that the object maps to.
	 *
	 * If applicable, add collections in the form of:
	 * $collectionObjectNamePlural = array();
	 *
	 * @var string
	 **/
	protected $checkStatement = null;

	/**
	 * An array of all the collections and their attributes.
	 *
	 * The information is used by CoughObject to write SQL queries,
	 * among other things.
	 *
	 * Format of "collection_name" => array of attributes
	 *
	 * TODO: Document that array of attributes. For now just look at the
	 * woc_Product_Generated class (at the defineCollections() function).
	 *
	 * @var array
	 **/
	protected $collectionDefinitions = array();
	
	/**
	 * An array of all the checked collections in form [collectionName] => [CoughCollection]
	 * 
	 * @var array
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
	 *
	 * CoughObject / __construct
	 * -------------------------
	 * required args:		n/a
	 * optional args:
	 * 						none	(constructs empty CoughObject)
	 * 						id		(constructs & checks CoughObject using passed value as key ID)
	 * 						assoc array / CoughCollection (constructs CoughObject, calls setFields() using the passed array)
	 * note:
	 * 						if the assoc array / collection includes the defined key for the object, the key will be set by setFields()
	 * desc:
	 * 						CoughObject() / __construct() is the class constructor
	 */
	public function __construct($fieldsOrID = array()) {
		// before chaining to construct, make sure you override initializeDefinitions() within the subclass
		//	and then invoke initializeDefinitions() in the constructor method.
		$this->initializeDefinitions();

		// Get our reference to the database object
		$this->db = DatabaseFactory::getDatabase($this->dbName);
		$this->db->selectDb($this->dbName);

		if (is_array($fieldsOrID)) {
			$this->initFields($fieldsOrID);
		} else if ($fieldsOrID != '') {
			$this->setKeyId($fieldsOrID);
			$this->check();
		} else {
			// do nothing
		}
		$this->finishConstruction();
	}

	protected function finishConstruction() {
		// override for special construction behaviour that is dependent on the object's state
	}

	/**
	 *
	 * protected _initializeDefinitions()
	 *	-------------------------
	 *		required args:		n/a
	 *		optional args:		n/a
	 *		desc:
	 *								_initializeDefinitions() sets the cough object's basic identity.
	 */
	protected function initializeDefinitions() {
		$this->initFieldsToDefaultValues();

		// the below method is used only if your class has a special check query other than a select by PK
		// $this->defineCheckStatement();

		// TODO: multi-table support has not been completed. -Tom
		// $this->defineTablesColumns(); // only needed for multi-table situations


		// defineObjects() and defineCollections() are run last to allow them
		// to make decisions based on the above definitions
		$this->defineObjects();
		$this->defineCollections();
	}
	
	public function setDb($db) {
		$this->db = $db;
	}

 	// definition methods for object initilization called by initializeDefinitions()
	protected function defineCheckStatement() {
		$this->checkStatement = ''; // override this line in subclass IF you need a special check query other than a select by PK
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
		$fields = $this->fields;
		foreach ($this->pkFieldNames as $fieldName) {
			unset($fields[$fieldName]);
			$this->fields[$fieldName] = null;
		}
		$this->modifiedFields = array_keys($fields);
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
		if (count($this->pkFieldNames) == 1) {
			$fieldName = $this->pkFieldNames[0];
			if (isset($this->fields[$fieldName])) {
				return $this->fields[$fieldName];
			} else {
				return null;
			}
		} else {
			return $this->getPk();
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
	 **/
	public function hasKeyId() {
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
	
	
	/*
	 * getCheckStatement()
	 * -------------------------
	 * required args:
	 * 						n/a
	 * optional args:
	 * 						n/a
	 * desc:
	 * 						getCheckStatement() returns the current statement that the "check()" method should run
	 * note:
	 * 						this statement is only used if the class has no primary key defined
	*/
	protected function getCheckStatement() {
		return ($this->checkStatement);
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
	 * Check if the given field name can be set to NULL.
	 * 
	 * If no information about the null status is available, then
	 * it always return true (to maintain backwards compatibility)
	 *
	 * @return boolean - true if null is allowed, false if not
	 * @author Anthony Bush
	 **/
	protected function isNullAllowed($fieldName) {
		if (isset($this->fieldDefinitions[$fieldName])) {
			// Temporary: the is_array check is there for backwards compatibility with old generated code.
			if (array_key_exists('default_value', $this->fieldDefinitions[$fieldName])) {
				return $this->fieldDefinitions[$fieldName]['is_null_allowed'];
			}
		}
		return true; // backwards compatible default value
	}
	
	/**
	 * Initializes all fields to their default values (without marking them
	 * modified).
	 *
	 * @return void
	 * @author Anthony Bush
	 * @todo deprecate this in favor of $fields being initialized to default values?
	 **/
	protected function initFieldsToDefaultValues() {
		foreach ($this->fieldDefinitions as $fieldName => $fieldDef) {
			if (array_key_exists('default_value', $fieldDef)) {
				$this->fields[$fieldName] = $fieldDef['default_value'];
			}
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
		$this->fields[$fieldName] = $value;
		$this->setModified($fieldName);
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
			if (isset($this->fieldDefinitions[$fieldName])) {
				$this->setField($fieldName, $fieldValue);
			} else if (($pos = strpos($fieldName, '.')) !== false) {
				// custom field
				// $joinTableName = substr($fieldName, 0, $pos);
				$joinFieldName = substr($fieldName, $pos + 1);
				$this->setJoinField($joinFieldName, $fieldValue);
			} else if (isset($this->$fieldName)) {
				$this->setCollection($fieldName, $fieldValue);
			} else {
				$this->setDerivedField($fieldName, $fieldValue);
			}
		}
	}
	
	/**
	 * Sets a read-only field; It's usually a derived field from a complex
	 * SQL query such as when using the defineCheckStatement() functionality.
	 *
	 * @param string $fieldName - the derived field name to set
	 * @param mixed $fieldValue - the value to store
	 * @return void
	 * @see defineCheckStatement()
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
	 * @see setDerivedField(), defineCheckStatement()
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

	/**
	 * Initializes the specified fields with given values without setting the
	 * fields as being modified.
	 *
	 * Meant to be called with values from the database, e.g. by the `check()`
	 * function. This way, a call to save() will not cause the values to be
	 * saved back to the database, a wasteful operation. All sets that should
	 * cause data to be actually saved should use setFields() and not this
	 * function.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function initFields($fields = array()) {
		$this->setFields($fields);

		// If the setting of the fields also set the primary key, then assume
		// we pulled from the database and clear the modified fields
		if ($this->hasKeyId()) {
			$this->resetModified();
		}
	}

	// ----------------------------------------------------------------------------------------------
	// GETTORS AND SETTORS block ENDS
	// ----------------------------------------------------------------------------------------------


	// ----------------------------------------------------------------------------------------------
	// object database methods / collection handling methods block BEGINS
	// ----------------------------------------------------------------------------------------------
	/*
	// ----------------------------------------------------------------------------------------------
	 * check()
	 * -------------------------
	 * required args:		n/a
	 * optional args:		n/a
	 * desc:
	 * 						check() gets an row from the class's corresponding table as an associative array uses it as an argument to invoke setFields()
	 * note:
	 * 						this will set the object's primary key and "row name"
	*/
	public function check() {
		if ($this->shouldCheckUsingCheckStatement()) {
			$sql = $this->getCheckStatement();
		} else if ($this->shouldCheckUsingTableName()) {
			$sql = 'SELECT * FROM ' . $this->dbName . '.' . $this->tableName . ' ' . $this->db->generateWhere($this->getPk());
		} else {
			// can't check.
			return false;
		}
		return $this->checkBySql($sql);
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
			$result = $this->db->doQuery($sql);
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
	 * Clear the list of modified fields
	 *
	 * @return void
	 * @author Wayne Wight
	 * @author Anthony Bush
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
	 * @return void
	 * @param string $fieldName - the name of the field (db_column_name) to mark modified
	 * @author Wayne Wight
	 **/
	protected function setModified($fieldName) {
		$this->modifiedFields[] = $fieldName;
	}

	/**
	 * Get a list of all of this object's modified values, indexed by their
	 * database field names/columns
	 *
	 * @return hash - a hash of all modified values in (dbColumn => currentValue) form
	 * @author Wayne Wight
	 **/
	protected function getModifiedFields() {
		$fields = array();
		foreach ($this->modifiedFields as $fieldName) {
			$fields[$fieldName] = $this->getField($fieldName);
		}
		return $fields;
	}
	
	/**
	 * If the object has a parent, then this method will update its foreign
	 * key with the value from the parent's primary key.
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
		
		// Save self first, in case the KeyID of a creation is needed by the saving of collections.
		if ($this->shouldCreate()) {
			$result = $this->create();
		} else {
			$result = $this->update();
		}

		// We have never said that this was okay, but this is how you would
		// save the objects automatically. It's worth noting that we never had
		// the ability to see which ones were checked (and thus potentially
		// modified) either, but we do now.

		//$this->saveCheckedObjects();

		// Save collections next, but only ones that have been checked.
		// (Alternately, we could do an additional conditional test to only
		// save ones that have also been gotten, but it is probably safe and more
		// effiecent to assume that if you are checking the collection you are
		// getting the collection.)
		$this->saveCheckedCollections();
		
		$this->saveJoinFields();
		
		$this->resetModified();
		return $result;
	}

	/**
	 * Indicates whether or not a save should create a new record or update an
	 * existing one.
	 *
	 * @return boolean - true = create new, false = update existing one
	 * @author Anthony Bush
	 **/
	protected function shouldCreate() {
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
		foreach ($this->collections as $collectionName => $collection) {
			$this->saveCollection($collectionName);
		}
	}
	
	/*
	 // ----------------------------------------------------------------------------------------------
	 * create()
	 * -------------------------
	 * required args:		n/a
	 * optional args:		n/a
	 * desc:
	 * 						create() writes a new row to the database and sets the object's key id with the returned database insert id
	 * note:
	 * 						this writes to the database any values that are currently set for the object's fields
	 */
	public function create() {
		
		// Loop through fields and build one without null values that aren't allowed
		$fields = array();
		// foreach ($this->fields as $fieldName => $fieldValue) {
		foreach ($this->getModifiedFields() as $fieldName => $fieldValue) {
			if (is_null($fieldValue) && !$this->isNullAllowed($fieldName)) {
				continue;
			}
			$fields[$fieldName] = $fieldValue;
		}
		
		$this->db->selectDb($this->dbName);
		if (!$this->hasKeyId()) {
			$id = $this->db->doInsert($this->tableName, $fields);
		} else {
			$this->db->doInsertOrUpdate($this->tableName, $fields, null, $this->getPk());
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
	 * Updates the database with modified values, if any.
	 *
	 * @return boolean - true
	 **/
	public function update() {
		$fields = $this->getModifiedFields();
		if (!empty($fields)) {
			$this->db->selectDb($this->dbName);
			$this->db->doUpdate($this->tableName, $fields, null, $this->getPk());
		}
		return true;
	}
	
	// ----------------------------------------------------------------------------------------------
	// object database methods / collection handling methods block ENDS
	// ----------------------------------------------------------------------------------------------


	// ----------------------------------------------------------------------------------------------
	// permittors, testors, & validators block BEGINS
	// ----------------------------------------------------------------------------------------------

	/**
	 * Returns true if the class is set to run check() based on its key id and
	 * not some special SQL statement
	 *
	 * @return boolean
	 **/
	protected function shouldCheckUsingTableName() {
		if ($this->tableName != '') {
			if (!empty($this->pkFieldNames)) {
				if ($this->hasKeyId()) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Returns true if the class is set to run check() based on some special pre-set SQL statement
	 *
	 * @return boolean
	 **/
	protected function shouldCheckUsingCheckStatement() {
		if ($this->checkStatement != '') {
			if (!empty($this->pkFieldNames)) {
				if ($this->hasKeyId()) {
					return true;
				}
			}
		}
		return false;
	}
	
	
	// ----------------------------------------------------------------------------------------------
	// permittors, testors, & validators block ENDS
	// ----------------------------------------------------------------------------------------------

	/**
	 * Checks the object using the configuration in `objectDefinitions`.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function _checkObject($objectName) {
		$objectInfo = &$this->objectDefinitions[$objectName];
		$this->objects[$objectName] = new $objectInfo['class_name']($this->$objectInfo['get_id_method']());
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
		$checkMethod = 'check' . ucwords($collectionName);
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
		$sql = 'DESCRIBE '. $this->dbName . "." . $def['join_table'];
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

	/**
	 * Set the specified collection to either:
	 *  - another collection
	 *  - or an array of elements
	 *  - or a single element
	 *
	 * where each element is either an ID of the current collection object type
	 * to be added or the object itself.
	 *
	 * @param string $collectionName - the name of the collection to set
	 * @param mixed $objectsOrIDs - the objects or IDs to set the collection to.
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function setCollection($collectionName, $objectsOrIDs) {
		$this->getCollection($collectionName)->set($objectsOrIDs);
	}

	/**
	 * Add to the specified collection to either:
	 *  - another collection
	 *  - or an array of elements
	 *  - or a single element
	 *
	 * where each element is either an ID of the current collection object type
	 * to be added or the object itself.
	 *
	 * @param string $collectionName - the name of the collection to add to.
	 * @param mixed $objectsOrIDs - the objects or IDs to add.
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function addToCollection($collectionName, $objectsOrIDs, $joinFields = null) {
		$this->getCollection($collectionName)->add($objectsOrIDs, $joinFields);
	}

	/**
	 * Remove from the specified collection either:
	 *  - another collection
	 *  - or an array of elements
	 *  - or a single element
	 *
	 * where each element is either an ID of the current collection object type
	 * to be added or the object itself.
	 *
	 * @param string $collectionName - the name of the collection to remove.
	 * @param mixed $objectsOrIDs - the objects or IDs to remove.
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function removeFromCollection($collectionName, $objectsOrIDs) {
		$this->getCollection($collectionName)->remove($objectsOrIDs);
	}
	
	/**
	 * Saves the specified collection, checking the type of collection first.
	 * (i.e. one-to-many or many-to-many).
	 *
	 * @param string $collectionName - the name of the collection to save.
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function saveCollection($collectionName) {
		if (isset($this->collectionDefinitions[$collectionName]['join_table'])) {
			$this->saveManyToManyCollection($collectionName);
		} else {
			$this->saveOneToManyCollection($collectionName);
		}
	}

	/**
	 * Saves the specified one-to-many collection.
	 *
	 * @param string $collectionName - the name of the collection to save.
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function saveOneToManyCollection($collectionName) {
		$collection = $this->getCollection($collectionName);
		
		// Call save on all collected items that still exist.
		$collection->save();

		// Update all removed items too by setting their foreign key id to NULL.
		foreach ($collection->getRemovedElements() as $elementID) {
			// Build a custom update query based on the colleciton attributes
			$def =& $this->collectionDefinitions[$collectionName];
			$collectionTable = $def['collection_table'];
			$relationKeyName = $def['relation_key'];
			$collectionKeyName = $def['collection_key'];
			$sql = 'UPDATE ' . $collectionTable . ' SET ' . $relationKeyName . ' = NULL WHERE ' . $collectionKeyName . ' = "' . $elementID . '"';
			if ( ! is_null($def['retired_column'])) {
				$sql .= ' AND ' . $def['retired_column'] . ' = "' . $def['is_not_retired'] . '"';
			}

			// Execute the query
			$this->db->query($sql);
		}

		// Update status in memory as saved.
		$collection->resetCollectionChanges();
	}

	/**
	 * Saves the specified many-to-many collection.
	 *
	 * @param string $collectionName - the name of the collection to save.
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function saveManyToManyCollection($collectionName) {
		$collection = $this->getCollection($collectionName);
		
		// Save each collected object
		$collection->save();

		// Get easy access to the current collection attributes
		$def =& $this->collectionDefinitions[$collectionName];
		
		/////////////////////////////////////////////////////////
		// Save all the removed elements (update the join table)
		/////////////////////////////////////////////////////////

		$removedElementIDs = $collection->getRemovedElements();
		if ( ! empty($removedElementIDs)) {

			if (isset($def['join_table_attr']) && isset($def['join_table_attr']['retired_column']))
			{
				$joinAttr =& $def['join_table_attr'];
				foreach ($removedElementIDs as $elementID) {

					$setFields = array(
						$joinAttr['retired_column'] => $joinAttr['is_retired']
					);

					$whereFields = array(
						  // 2007-04-16/AWB: We should probably stop the remove functionality, or have it work on primary keys... or have the collection perform the remove. For example, allow end-users to override some remove-like function that specifies how a remove should occur, e.g. maybe a remove is $collectedElement->setJoinField('is_retired', 1); or maybe a remove is $collectedElement->setIsRetired(1); or $collectedElement->markForDelete();
						  // $def['join_primary_key'] => $elementID
						  $def['relation_key']   => $this->getKeyId()
						, $def['collection_key'] => $elementID
						, $joinAttr['retired_column']   => $joinAttr['is_not_retired']
					);

					$this->db->doUpdate($def['join_table'], $setFields, null, $whereFields);
				}
			}
			// If there is no retired column specified, then we can't remove..
			else
			{
				// TODO: Add documentation for this... Also, add an option to the generator that allows this attribute to be added automatically for tables with no retired column.
				if (isset($def['allow_deletes']) && $def['allow_deletes']) {
					$whereFields = array(
						  $def['relation_key']   => $this->getKeyId()
						, $def['collection_key'] => $elementID
					);
					$this->db->doDelete($def['join_table'], $whereFields);
				} else {
					// No retired column and deletes are not allowed, i.e. we have no way to "save" the current state.
					throw new Exception('No retired column set for collection "' . $collectionName . '" (join table "' . $def['join_table'] . '"). The `allow_deletes` attributes is also not set. Either add a retired column to ' . $def['join_table'] . ' or enable deletes for this join table in the Cough model.');
				}
			}
		}

		/////////////////////////////////////////////////////////
		// Save all the added elements (update the join table)
		/////////////////////////////////////////////////////////

		/* 2007-02-19/AWB: This is now handled by the element itself.
		// First, build an array of value arrays to insert
		$values = array();
		foreach ($collection->getAddedElements() as $elementID) {
			$values[] = array($elementID, $this->getKeyId());
		}

		// If we have some values to insert, insert them
		if ( ! empty($values)) {

			// What fields are we inserting?
			$fields = array($def['collection_key'], $def['relation_key']);

			// Do the insert
			$this->db->insertMultiple($def['join_table'], $fields, $values);
		}
		*/
		
		$collection->resetCollectionChanges();
	}
	
	protected function saveJoinFields() {
		
		if ($this->isJoinTableNew) {

			$this->setJoinFields($this->getPk());
			$this->setJoinFields($this->getCollector()->getPk());
			$this->db->doInsertOnDupUpdate($this->getJoinTableName(),$this->getJoinFields());
			
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
			$this->db->doInsertOrUpdate($this->getJoinTableName(),$fieldsToSave,null,$whereFields);
		}
	}

	/**
	 * Returns the function name of the getter for the given field name.
	 *
	 * TODO: Instead of calling getTitleCase, have the generator set the titlecase in the columns array.
	 * Also, it doesn't work for the primary key getter.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function getGetter($fieldName) {
		$potentialGetter = 'get' . String::getTitleCase($fieldName);
		if (method_exists($this, $potentialGetter)) {
			return $potentialGetter;
		} else {
			return null;
		}
	}

	/**
	 * Returns the function name of the setter for the given field name.
	 *
	 * TODO: Instead of calling getTitleCase, have the generator set the titlecase in the columns array.
	 * Also, it doesn't work for the primary key setter.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function getSetter($fieldName) {
		$potentialGetter = 'set' . String::getTitleCase($fieldName);
		if (method_exists($this, $potentialGetter)) {
			return $potentialGetter;
		} else {
			return null;
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
		// Allow: getJoin_Customer_RelationshipStartDate calls, which will invoke getJoinField('relationship_start_date') for now.
		if (strpos($method, 'getJoin_') === 0)
		{
			$methodPieces = explode('_', $method);
			if (count($methodPieces) == 3) {
				$field = String::convertCamelCaseToUnderscore($methodPieces[2]);
				return $this->getJoinField($field);
			}
		}
		// Allow: setJoin_Customer_RelationshipStartDate calls, which will invoke setJoinField('relationship_start_date', $value) for now.
		else if (strpos($method, 'setJoin_') === 0)
		{
			$methodPieces = explode('_', $method);
			if (count($methodPieces) == 3) {
				$field = String::convertCamelCaseToUnderscore($methodPieces[2]);
				if (isset($args[0])) {
					$value = $args[0];
				} else {
					$value = null;
				}
				return $this->setJoinField($field, $value);
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
}


?>