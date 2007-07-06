<?php

/**
 * CoughObject is the foundation for which all other "Model" / "ORM" classes
 * extend.  There will usually be one class extending CoughObject for each table
 * in the database that an ORM is needed for.
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
	protected $columns = array();

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
	 * The db column name of the primary key field.
	 *
	 * @var string
	 **/
	protected $keyColumn;

	/**
	 * The name of the database the table(s) is/are in.
	 *
	 * @var string
	 **/
	protected $dbName;

	/**
	 * The name of table the object maps to, if applicable.
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
	 * An array of each database tables' column names.
	 *
	 * Format of ["db table name"]["db column name"] => true
	 *
	 * NOTE: This and its related functions are not currently used.
	 *
	 * @var array of arrays
	 **/
	protected $tablesColumns = array();

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
	 * Format of "object_name" => array of attributes
	 *
	 * TODO: Document that array of attributes. For now just look at the
	 * woc_Product_Generated class (at the defineObjects() function).
	 *
	 * @var array
	 **/
	protected $objects = array();

	/**
	 * An array of all the currently checked objects.
	 *
	 * An object that has not populated will not be be in the array at all.
	 *
	 * Format of "object_name" => true
	 *
	 * @var string
	 **/
	protected $checkedObjects = array();

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

		if (get_class($fieldsOrID) && is_subclass_of($fieldsOrID, 'CoughCollection')) {
			$this->setFieldsFromCollection($fieldsOrID);
		} else if (is_array($fieldsOrID)) {
			$this->initFields($fieldsOrID);
		// Should we handle case of when an object is passed in?
		// } else if (get_class($fieldsOrID) && is_subclass_of($fieldsOrID, 'CoughObject')) {
		// 	// Set to a copy or by reference to the other object?
		// 	$this->setKeyID($fieldsOrID->getKeyID());
		// 	$this->check();
		//  // Or user can use:
		// 	//$new_product = clone $product;
		//  // or
		//  //$new_product = $product;
		} else if ($fieldsOrID != '') {
			$this->setKeyID($fieldsOrID);
			$this->check();
		} else {
			// do nothing
		}
		$this->finishConstruction();
	}

	protected function finishConstruction() {
		// override for special construction behaviour that is dependent on the object's state
		// $this->checkAllObjects();
		// $this->initCollections();
		//echo ("CoughObject::finishConstruction() for " . get_class($this) . "<br />\n");
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
		$this->defineDBName();
		$this->defineColumns();
		$this->defineKeyColumn();
		$this->defineNameColumn();
		$this->defineTableName();
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
	protected function defineDBName() {
		$this->dbName = ''; // override this line in subclass
	}
	protected function defineColumns() {
		$this->columns = array(); // override this line in subclass
	}
	protected function defineKeyColumn() {
		$this->keyColumn = ''; // override this line in subclass
	}
	protected function defineTableName() {
		$this->tableName = ''; // override this line in subclass
	}
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
		
		$this->setKeyID(null);
		// Mark all fields as having been modified (except key ID) so that a call to save() will complete the clone.
		$fields = $this->fields;
		unset($fields[$this->getKeyName()]);
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
			$thisFields = $this->getFields();
			unset($thisFields[$this->getKeyName()]);
			$thoseFields = $coughObject->getFields();
			unset($thoseFields[$coughObject->getKeyName()]);
			if ($thisFields == $thoseFields) {
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
	
	/*
	 * setKeyID()
	 * -------------------------
	 * required args:
	 * 						id
	 * optional args:
	 * 						n/a
	 * desc:
	 * 						setKeyID() sets the object's primary key id to the passed value
	*/
	public function setKeyID($id) {
		$this->setField($this->getKeyName(), $id);
	}

	/**
	 * Allows you to insert a record when you already know the primary key id.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function setPreknownKeyID($id) {
		$this->setKeyID($id);
		$this->isPreknownKeyIdSet = true;
	}

	/*
	 * getKeyName()
	 * -------------------------
	 * required args:
	 * 						n/a
	 * optional args:
	 * 						n/a
	 * desc:
	 * 						getKeyName() returns the name of the class's primary key column
	*/
	public function getKeyName() {
		return ($this->keyColumn);
	}
	
	/*
	 * getKeyID()
	 * -------------------------
	 * required args:
	 * 						n/a
	 * optional args:
	 * 						n/a
	 * desc:
	 * 						getKeyID() returns the current value of the object's primary key id
	*/
	public function getKeyID() {
		$keyColumn = $this->getKeyName();
		if (isset($this->fields[$keyColumn])) {
			return $this->fields[$keyColumn];
		} else {
			return null;
		}
	}
	
	/**
	 * Returns the primary key as an array of [field_name] => field_value
	 * 
	 * If the key is a multi-field primary key, then the array will contain
	 * more than one element, obviously.
	 *
	 * @return array - primary key as [field_name] => field_value
	 * @author Anthony Bush
	 **/
	public function getKeyAsArray() {
		// TODO: Cough: Update this once we get multi-field primary key going...
		return array($this->getKeyName() => $this->getKeyID());
	}
	
	/**
	 * Abstract out the getKeyID() === null tests. Eventually, we'll have multi-key
	 * PK support, making this function more useful.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function hasKeyID() {
		if ($this->getKeyID() === null) {
			return false;
		} else {
			return true;
		}
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
	/*
	 * getField()
	 * -------------------------
	 * required args:
	 * 						field name
	 * desc:
	 * 						getField() returns the current value of the field name passed
	*/
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
		if (isset($this->columns[$fieldName])) {
			// Temporary: the is_array check is there for backwards compatibility with old generated code.
			if (is_array($this->columns[$fieldName]) && array_key_exists('default_value', $this->columns[$fieldName])) {
				return $this->columns[$fieldName]['is_null_allowed'];
			}
		}
		return true; // backwards compatible default value
	}
	
	public function setFieldToDefaultValue($fieldName) {
		$this->setField($fieldName, $this->getFieldDefaultValue($fieldName));
	}
	
	public function getFieldDefaultValue($fieldName) {
		if (isset($this->columns[$fieldName])) {
			// Temporary: the is_array check is there for backwards compatibility with old generated code.
			if (is_array($this->columns[$fieldName]) && array_key_exists('default_value', $this->columns[$fieldName])) {
				return $this->columns[$fieldName]['default_value'];
			}
		}
		return null; // backwards compatible default value
	}
	
	protected function initFieldsToDefaultValues() {
		foreach ($this->columns as $fieldName => $fieldAttr) {
			// Temporary: the is_array check is there for backwards compatibility with old generated code.
			if (is_array($fieldAttr) && array_key_exists('default_value', $fieldAttr)) {
				$this->fields[$fieldName] = $fieldAttr['default_value'];
			}
		}
	}
	
	
	/*
	 * setField()
	 * -------------------------
	 * required args:
	 * 						field name
	 * 						field value
	 * desc:
	 * 						setField() sets the current value of the field name passed to the value that was passed
	*/
	protected function setField($fieldName, $value) {
		$this->fields[$fieldName] = $value;
		$this->setModified($fieldName);
	}

	/*
	 * setFields()
	 * -------------------------
	 * required args:
	 * 						n/a
	 * optional args:
	 * 						fields [assoc array]
	 * desc:
	 * 						setFields() sets the current value of the all the class's defined columns equal to the values passed in the fields assoc array
	 * note:
	 * 						1. if a field passed in the fields assoc array isn't in the class's defined columns, it will NOT BE SET
	 * 						2. setFields automatically sets the object's primary key and "row name" if those values are passed in the fields assoc array
	*/
	public function setFields($fields = array()) {
		foreach ( $fields as $fieldName => $fieldValue ) {
			if (isset($this->columns[$fieldName])) {
				if ($this->isKey($fieldName)) {
					$this->setKeyID($fieldValue);
				} else {
					$this->setField($fieldName, $fieldValue);
				}
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
		if ( ! is_null($this->getKeyID())) {
			$this->resetModified();
		}
	}

	/**
	 * Undocumented function
	 *
	 * AWB:
	 *     - When is this actually used?
	 *     - protected / public?
	 *
	 * @param CoughCollection $c
	 * @return void
	 **/
	protected function setFieldsFromCollection($c) {
		if (is_subclass_of($c,'CoughCollection')) {
			$i = $c->getIterator();
			while ($i->valid()) {
				$fieldName = $i->key();
				$fieldValue = $i->current();
				if (isset($this->columns[$fieldName])) {
					if ($this->isKey($fieldName)) {
						$this->setKeyID($fieldValue);
					} else {
						$this->setField($fieldName, $fieldValue);
					}
				}
				$i->next();
			}
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
			// 2007-03-23/AWB: This has previously been un-used, and now that it will be, it needs to work by allowing FULL SQL customization.
			// $sql = 'SELECT * FROM ' . $this->getCheckStatement() . ' WHERE ' . $this->getKeyName() . ' = "' . $this->getKeyID() . '"';
			$sql = $this->getCheckStatement();
		} else if ($this->shouldCheckUsingTableName()) {
			$sql = 'SELECT * FROM ' . $this->dbName . '.' . $this->tableName . ' WHERE ' . $this->getKeyName() . ' = ' . $this->db->quote($this->getKeyID());
		} else {
			// can't check.
			return false;
		}

		$this->db->selectDb($this->dbName);
		$result = $this->db->query($sql);
		if ($result->numRows() == 1) {
			$this->initFields($result->getRow());
			$this->setCheckReturnedResult(true);
		} else if ($result->numRows() == 0) {
			// check failed because the unique dataset couldn't be selected
			$this->setCheckReturnedResult(false);
		} else if ($result->numRows() > 1) {
			// check failed because the dataset returned was nonunique
			//		todo: add optional summarizing behaviour
			$this->setCheckReturnedResult(false);
		}
		$result->freeResult();
		return $this->didCheckReturnResult();
	}

	/**
	 * Provides a way to `check` by a unique key other than the primary key.
	 *
	 * @param string $uniqueKey - the db_column_name to compare with
	 * @param string $uniqueValue - the value to look for
	 * @return boolean - true if one row returned, false otherwise.
	 * @author Anthony Bush
	 **/
	public function checkBy($uniqueKey, $uniqueValue) {
		if ( ! empty($uniqueKey)) {
			$whereSQL = $uniqueKey . " = " . $this->db->quote($uniqueValue);

			// Run the query
			$sql = 'SELECT * FROM ' . $this->dbName . '.' . $this->tableName . ' WHERE ' . $whereSQL;
			$result = $this->db->query($sql);
			if ($result->numRows() == 1) {
				$this->initFields($result->getRow());
				$this->setCheckReturnedResult(true);
			} else {
				$this->setCheckReturnedResult(false);
			}
			$result->freeResult();
		} else {
			$this->setCheckReturnedResult(false);
		}
		return $this->didCheckReturnResult();
	}

	/**
	 * Provides a way to `check` by an array of "key" => "value" pairs.
	 *
	 * @param array $where - an array of "key" => "value" pairs to search for
	 * @param boolean $allowManyRows - set to true if you want to initialize from a record even if there was more than one record returned.
	 * @return boolean - true if initialized object with data, false otherwise.
	 * @author Anthony Bush
	 **/
	public function checkByArray($where = array(), $allowManyRows = false) {
		if ( ! empty($where)) {
			$this->db->selectDb($this->dbName);
			$result = $this->db->doSelect($this->tableName, array(), $where);
			if ($result->numRows() == 1 || ($allowManyRows && $result->numRows() > 1)) {
				$this->initFields($result->getRow());
				$this->setCheckReturnedResult(true);
			} else {
				$this->setCheckReturnedResult(false);
			}
			$result->freeResult();
		} else {
			$this->setCheckReturnedResult(false);
		}
		return $this->didCheckReturnResult();
	}
	
	/**
	 * Provides a way to `check` by custom SQL.
	 *
	 * @param string $sql - custom SQL to use during the check
	 * @param boolean $allowManyRows - set to true if you want to initialize from a record even if there was more than one record returned.
	 * @return boolean - true if initialized object with data, false otherwise.
	 * @author Anthony Bush
	 **/
	public function checkBySql($sql, $allowManyRows = false) {
		if ( ! empty($sql)) {
			$this->db->selectDb($this->dbName);
			$result = $this->db->doQuery($sql);
			if ($result->numRows() == 1 || ($allowManyRows && $result->numRows() > 1)) {
				$this->initFields($result->getRow());
				$this->setCheckReturnedResult(true);
			} else {
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
	 * Creates a new entry if needed, otherwise it updates an existing one.
	 * During an update, it only updates the modified fields. If you pass a
	 * list of fields to update, it will update those fields instead.
	 *
	 * @return boolean - the result of the create/update.
	 * @author Anthony Bush
	 **/
	public function save($fieldsToUpdate = null) {
		
		// Update the child with it's parent id
		if ($this->hasCollector()) {
			$collector = $this->getCollector();
			foreach ($this->objects as $objProperties) {
				if ($collector instanceof $objProperties['class_name']) {
					$getter = $objProperties['get_id_method'];
					$setter = $getter;
					$setter[0] = 's';
					if ($this->$getter() === null) {
						$this->$setter($collector->getKeyId());
					}
					break;
				}
			}
		}
		
		// Check for valid data.
		$this->validateData($this->fields);
		if ( ! $this->isDataValid()) {
			return false;
		}
		
		// Save self first, in case the KeyID of a creation is needed by the saving of collections.
		if ($this->shouldCreate()) {
			$result = $this->create();
		} else {
			$result = $this->update($fieldsToUpdate);
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
		if ($this->getKeyID() === null || $this->isPreknownKeyIdSet === true) {
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
		foreach (array_keys($this->checkedObjects) as $objectName) {
			$this->$objectName->save();
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
		foreach (array_keys($this->checkedCollections) as $collectionName) {
			$this->saveCollection($collectionName);
		}
	}

	/**
	 * Saves everything. Probably shouldn't use. Just run save().
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function saveAll() {
		$this->save();
		$this->saveCheckedObjects();
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
		if ($this->getKeyID() === null) {
			$id = $this->db->doInsert($this->tableName, $fields);
		} else {
			$this->db->doInsertOrUpdate($this->tableName, $fields, null, $this->getKeyAsArray());
			$id = null;
		}
		if ($id != '') {
			$this->setKeyID($id);
			return true;
		} else {
			return false;
		}
	}
	
	/*
	// ----------------------------------------------------------------------------------------------
	 * update()
	 * -------------------------
	 * required args:		fieldsToUpdate as fieldName [string] or as fields [array]
	 * optional args:		n/a
	 * desc:
	 * 						update() saves the object's current value of the passed field names to the database
	 * note:
	 * 						PLEASE READ!!!
	 * 						you pass the NAMES OF THE FIELDS TO BE SAVED, -not- an associative array of the field names => field values.
	 * 						THIS IS AN IMPORTANT DIFFERENCE!!!
	*/
	public function update($fieldsToUpdate = null) {

		$fields = array();
		if (is_null($fieldsToUpdate)) {
			// Update only the fields that were changed, if any.
			$fields = $this->getModifiedFields();
			// If no fields because nothing was modified, return true.
			if (empty($fields)) {
				return true;
			}
		} else if (is_array($fieldsToUpdate)) {
			// We are updating customized fields
			foreach ($fieldsToUpdate as $fieldName) {
				$fields[$fieldName] = $this->getField($fieldName);
			}
		} else {
			// We are updating only one field
			$fields[$fieldsToUpdate] = $this->getField($fieldsToUpdate);
		}
		if (count($fields) > 0) {
			$this->db->selectDb($this->dbName);
			$this->db->doUpdate($this->tableName, $fields, null, $this->getKeyAsArray());
			return true;
		} else {
			// no legal fields passed! do nothing.
			return false;
		}
	}
	/*
	// ----------------------------------------------------------------------------------------------
	 * populateCollection()
	 * -------------------------
	 * required args:		name of collection class
	 * 						name of element class
	 * 						sql statement that gets rows to populate collection with
	 * optional args:		n/a
	 * desc:
	 * 						populateCollection() gets a set of rows from the database, constructs each row as an object, and pushes
	 * 						that row onto the defined array name. This allows for easy population of object collections.
	 * note:
	 * 						populateCollection uses the primary key of each returned row as the array key for the collection
	*/
	public function populateCollection($collectionName, $elementName='', $sql='', $orderBySQL='') {
		if (is_null($this->$collectionName)) {
			$this->initCollection($collectionName);
		}
		if (is_object($this->$collectionName) && is_subclass_of($this->$collectionName,'CoughCollection')) {
			$this->$collectionName->populateCollection($elementName, $sql, $orderBySQL);
			$this->checkedCollections[$collectionName] = true;
		} else {
			throw new Exception("CoughObject::populateCollection did not have a collection object ($collectionName) to populate with $elementName elements.");
		}
	}
	// ----------------------------------------------------------------------------------------------
	// object database methods / collection handling methods block ENDS
	// ----------------------------------------------------------------------------------------------


	// ----------------------------------------------------------------------------------------------
	// permittors, testors, & validators block BEGINS
	// ----------------------------------------------------------------------------------------------
	/*
	 * isKey()
	 * -------------------------
	 * required args:		fieldName
	 * optional args:		n/a
	 * desc:
	 * 						isKey() returns true if the passed value is the same as the class's key column name
	*/
	public function isKey($fieldName) {
		if ($this->keyColumn == $fieldName) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns true if the class is set to run check() based on its key id and
	 * not some special SQL statement
	 *
	 * @return boolean
	 **/
	protected function shouldCheckUsingTableName() {
		if ($this->tableName != '') {
			if ($this->getKeyName()) {
				if ($this->hasKeyID()) {
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
			if ($this->getKeyName()) {
				if ($this->hasKeyID()) {
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
	 * Instantiates all objects and collections AND checks the database for
	 * them.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function superCheck() {
		$this->checkAllObjects();
		$this->checkAllCollections();
	}

	/**
	 * Checks all the objects associated with the object
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function checkAllObjects() {
		//echo ("CoughObject::checkAllObjects() called for " . get_class($this) . "<br />\n");
		foreach (array_keys($this->objects) as $objectName) {
			$this->checkObject($objectName);
		}
		//echo ("CoughObject::checkAllObjects() finished<br />\n");
	}

	/**
	 * Instantiates the given object name, using the information
	 * specified in the `objects` array.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function checkObject($objectName) {
		$objectInfo = $this->objects[$objectName];
		$this->$objectName = new $objectInfo['class_name']($this->$objectInfo['get_id_method']());
		$this->checkedObjects[$objectName] = true;
	}

	/**
	 * Returns the specified object for use.
	 *
	 * If the object has not been checked/instantiated, then it gets checked.
	 *
	 * @param string $objectName - the name of the object to check and get
	 * @return CoughObject - the requested object
	 * @author Anthony Bush
	 **/
	protected function checkOnceAndGetObject($objectName) {
		$this->readyObject($objectName);
		return $this->$objectName;
	}

	protected function readyObject($objectName) {
		if ( ! $this->isObjectChecked($objectName)) {
			$this->checkObject($objectName);
		}
	}

	/**
	 * Instantiates each collection that is part of this object, according to
	 * what defineCollections() set the `collections` array to.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function initAllCollections() {
		foreach (array_keys($this->collections) as $collectionName) {
			$this->initCollection($collectionName);
		}
	}

	/**
	 * Instantiates the given collection name.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function initCollection($collectionName) {
		$collection =& $this->collections[$collectionName];
		$this->$collectionName = new $collection['collection_class']();
		
		if (isset($collection['join_table'])) {
			$this->$collectionName->setCollector($this, CoughCollection::MANY_TO_MANY, $collection['join_table']);
		} else {
			$this->$collectionName->setCollector($this, CoughCollection::ONE_TO_MANY);
		}
	}

	/**
	 * Alias of `checkAllCollections`
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function populateAllCollections() {
		$this->checkAllCollections();
	}

	/**
	 * Checks all the collections associated with the object
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function checkAllCollections() {
		foreach (array_keys($this->collections) as $collectionName) {
			$this->checkCollection($collectionName);
		}
	}

	/**
	 * Checks/populates the collection for the specified collection name.
	 *
	 * @param string $collectionName - the name of the collection to check
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function checkCollection($collectionName) {
		if (is_null($this->$collectionName)) {
			$this->initCollection($collectionName);
		}
		// Only populate the collection (i.e. run a select on the database) if either a custom check function is set OR we have a KeyID.
		if (isset($this->collections[$collectionName]['custom_check_function'])) {
			$customCheckFunction = $this->collections[$collectionName]['custom_check_function'];
			$this->$customCheckFunction();
		} else if ($this->hasKeyID()) {
			// 2007-04-16/AWB: We should be calling the generated check method so that anytime the collection is checked it goes through a single point. This change fixes that issue:
			$checkMethod = 'check' . ucwords($collectionName);
			$this->$checkMethod();
			// if (isset($this->collections[$collectionName]['join_table'])) {
			// 	$this->checkManyToManyCollection($collectionName);
			// } else {
			// 	$this->checkOneToManyCollection($collectionName);
			// }
		} else {
			// 2007-06-25/AWB: Update checked collection status for collections with no parent yet. For example, you create an order and then "check" the order line collection (even though you have no order id yet); the collection should be empty, and it was, but it should also be marked checked, and now it is.
			$this->checkedCollections[$collectionName] = true;
		}
	}

	/**
	 * Checks/populates the collection for the specified one-to-many collection
	 * name.
	 *
	 * @param string $collectionName - the name of the collection to check
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function checkOneToManyCollection($collectionName) {
		$collection =& $this->collections[$collectionName];

		$sql = '
			SELECT *
			FROM ' . $collection['collection_table'] . '
			WHERE ' . $collection['relation_key'] . ' = ' . $this->db->quote($this->getKeyID());

		if (isset($collection['retired_column']) && ! empty($collection['retired_column'])) {
			$sql .= '
				AND ' . $collection['retired_column'] . ' = ' . $this->db->quote($collection['is_not_retired']);
		}

		$this->populateCollection($collectionName, $collection['element_class'], $sql);
	}

	/**
	 * Checks/populates the collection for the specified many-to-many
	 * collection name.
	 *
	 * @param string $collectionName - the name of the collection to check
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function checkManyToManyCollection($collectionName) {
		$collection =& $this->collections[$collectionName];

		$sql = '
			SELECT ' . $collection['collection_table'] . '.*' . $this->getJoinSelectSql($collectionName) . '
			FROM ' .$collection['join_table'] . '
			INNER JOIN ' . $collection['collection_table'] . ' ON ' . $collection['join_table'] . '.' . $collection['collection_key']
				. ' = ' . $collection['collection_table'] . '.' . $collection['collection_key'] . '
			WHERE ' . $collection['join_table'] . '.' . $collection['relation_key'] . ' = ' . $this->db->quote($this->getKeyID());
		
		if (isset($collection['retired_column']) && ! empty($collection['retired_column'])) {
			$sql .= '
				AND ' . $collection['collection_table'] . '.' . $collection['retired_column'] . ' = ' . $this->db->quote($collection['is_not_retired']);
		}
		
		if (isset($collection['join_table_attr'])) {
			$joinAttr =& $collection['join_table_attr'];
			if (isset($joinAttr['retired_column']) && ! empty($joinAttr['retired_column'])) {
				$sql .= '
					AND ' . $collection['join_table'] . '.' . $joinAttr['retired_column'] . ' = ' . $this->db->quote($joinAttr['is_not_retired']);
			}
		}

		$this->populateCollection($collectionName, $collection['element_class'], $sql);
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
		$collection =& $this->collections[$collectionName];
		
		// Get extra join fields on-the-fly
		$joinFields = array();
		$sql = 'DESCRIBE '. $this->dbName . "." . $collection['join_table'];
		$result = $this->db->query($sql);
		while ($row = $result->getRow()) {
			$joinFieldName = $row['Field'];
			$joinFields[] = $collection['join_table'] . '.' . $joinFieldName . ' AS `' . $collection['join_table'] . '.' . $joinFieldName . '`';
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
	 * This also means the collection has been populated.
	 *
	 * @return boolean - true if collection has been populated, false if not.
	 * @author Anthony Bush
	 **/
	protected function isCollectionChecked($collectionName) {
		// We could ask the collection itself if it has been populated:
		//     $this->$collectionName->isPopulated();
		// BUT, we would need to make sure the object is even instantiated first...
		//     if (is_null($this->$collectionName)) {
		//         return false;
		//     } else {
		//         return $this->$collectionName->isPopulated();
		//     }
		// HOWEVER, we still want to keep a list of populated collections ourselves...
		// It makes the saving more efficient because then we don't have to ask
		// every single collection if they are populated, we simply iterate through
		// our "is populated" array.

		return isset($this->checkedCollections[$collectionName]);
	}

	/**
	 * Tells whether or not the given object name has been checked.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function isObjectChecked($objectName) {
		return isset($this->checkedObjects[$objectName]);
	}

	/**
	 * Gets the checked objects
	 *
	 * @return array - the checked objects; each element is a string containing the object name.
	 * @author Anthony Bush
	 **/
	public function getCheckedObjects() {
		$checkedObjects = array();
		foreach (array_keys($this->objects) as $objectName) {
			if ($this->isObjectChecked($objectName)) {
				$checkedObjects[] = $objectName;
			}
		}
		return $checkedObjects;
	}

	/**
	 * Gets the non-checked objects
	 *
	 * @return array - the non-checked objects; each element is a string containing the object name.
	 * @author Anthony Bush
	 **/
	public function getNonCheckedObjects() {
		$nonCheckedObjects = array();
		foreach (array_keys($this->objects) as $objectName) {
			if ( ! $this->isObjectChecked($objectName)) {
				$nonCheckedObjects[] = $objectName;
			}
		}
		return $nonCheckedObjects;
	}

	/**
	 * Gets the checked collections
	 *
	 * @return array - the checked collections; each element is a string containing the collection name.
	 * @author Anthony Bush
	 **/
	public function getCheckedCollections() {
		$checkedCollections = array();
		foreach (array_keys($this->collections) as $collectionName) {
			if ($this->isCollectionChecked($collectionName)) {
				$checkedCollections[] = $collectionName;
			}
		}
		return $checkedCollections;
	}

	/**
	 * Gets the non-checked collections
	 *
	 * @return array - the non-checked collections; each element is a string containing the collection name.
	 * @author Anthony Bush
	 **/
	public function getNonCheckedCollections() {
		$nonCheckedCollections = array();
		foreach (array_keys($this->collections) as $collectionName) {
			if ( ! $this->isCollectionChecked($collectionName)) {
				$nonCheckedCollections[] = $collectionName;
			}
		}
		return $nonCheckedCollections;
	}

	/**
	 * Returns the specified collection for use.
	 *
	 * If the collection has not been checked/populated, then it gets checked.
	 *
	 * @param string $collectionName - the name of the collection to check and get
	 * @return CoughCollection - the requested collection object
	 * @author Anthony Bush
	 **/
	protected function checkOnceAndGetCollection($collectionName) {
		$this->readyCollection($collectionName);
		return $this->getCollection($collectionName);
	}

	/**
	 * Returns the specified collection for use.
	 *
	 * @param $collectionName - the name of the collection to get
	 * @return CoughCollection - the requested collection object
	 * @author Anthony Bush
	 **/
	protected function getCollection($collectionName) {
		return $this->$collectionName;
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
		$this->readyCollection($collectionName);
		$this->$collectionName->set($objectsOrIDs);
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
		$this->readyCollection($collectionName);
		$this->$collectionName->add($objectsOrIDs, $joinFields);
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
		$this->readyCollection($collectionName);
		$this->$collectionName->remove($objectsOrIDs);
	}

	protected function readyCollection($collectionName) {
		if ( ! $this->isCollectionChecked($collectionName)) {
			$this->checkCollection($collectionName);
		}
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
		if (isset($this->collections[$collectionName]['join_table'])) {
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
		// TODO: Cough: Make sure that once we update the collection to handle
		// it's collected object's attributes, that we make sure the logic here
		// still works. PROBABLY, the case will be that the following logic
		// will simply be moved into the collections save method.

		// Call save on all collected items that still exist.
		$this->$collectionName->save();

		// Update all removed items too by setting their foreign key id to NULL.
		foreach ($this->$collectionName->getRemovedElements() as $elementID) {
			// Build a custom update query based on the colleciton attributes
			$collectionAttr =& $this->collections[$collectionName];
			$collectionTable = $collectionAttr['collection_table'];
			$relationKeyName = $collectionAttr['relation_key'];
			$collectionKeyName = $collectionAttr['collection_key'];
			$sql = 'UPDATE ' . $collectionTable . ' SET ' . $relationKeyName . ' = NULL WHERE ' . $collectionKeyName . ' = "' . $elementID . '"';
			if ( ! is_null($collectionAttr['retired_column'])) {
				$sql .= ' AND ' . $collectionAttr['retired_column'] . ' = "' . $collectionAttr['is_not_retired'] . '"';
			}

			// Execute the query
			$this->db->query($sql);
		}

		// Update status in memory as saved.
		$this->$collectionName->resetCollectionChanges();
	}

	/**
	 * Saves the specified many-to-many collection.
	 *
	 * @param string $collectionName - the name of the collection to save.
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function saveManyToManyCollection($collectionName) {
		// Save each collected object
		// NOTE:
		//    This could be considered inefficient, but it's impossible to
		//    know if any of the collected elements where changed. The user
		//    could have done it without us knowing via something like:
		//       $product->getOs_Collection()[2]->setName('FreeBSD');
		//    Or in a more practical way:
		//       $oses = $product->getOs_Collection();
		//       foreach ($oses as $osID => $os) {
		//           $os->setLastModifiedDatetime('0000-00-00 00:00:00');
		//       }
		$this->$collectionName->save();

		// Get easy access to the current collection attributes
		$collection =& $this->collections[$collectionName];

		/////////////////////////////////////////////////////////
		// Save all the removed elements (update the join table)
		/////////////////////////////////////////////////////////

		$removedElementIDs = $this->$collectionName->getRemovedElements();
		if ( ! empty($removedElementIDs)) {

			if (isset($collection['join_table_attr']) && isset($collection['join_table_attr']['retired_column']))
			{
				$joinAttr =& $collection['join_table_attr'];
				foreach ($removedElementIDs as $elementID) {

					$setFields = array(
						$joinAttr['retired_column'] => $joinAttr['is_retired']
					);

					$whereFields = array(
						  // 2007-04-16/AWB: We should probably stop the remove functionality, or have it work on primary keys... or have the collection perform the remove. For example, allow end-users to override some remove-like function that specifies how a remove should occur, e.g. maybe a remove is $collectedElement->setJoinField('is_retired', 1); or maybe a remove is $collectedElement->setIsRetired(1); or $collectedElement->markForDelete();
						  // $collection['join_primary_key'] => $elementID
						  $collection['relation_key']   => $this->getKeyID()
						, $collection['collection_key'] => $elementID
						, $joinAttr['retired_column']   => $joinAttr['is_not_retired']
					);

					$this->db->doUpdate($collection['join_table'], $setFields, null, $whereFields);
				}
			}
			// If there is no retired column specified, then we can't remove..
			else
			{
				// TODO: Add documentation for this... Also, add an option to the generator that allows this attribute to be added automatically for tables with no retired column.
				if (isset($collection['allow_deletes']) && $collection['allow_deletes']) {
					$whereFields = array(
						  $collection['relation_key']   => $this->getKeyID()
						, $collection['collection_key'] => $elementID
					);
					$this->db->doDelete($collection['join_table'], $whereFields);
				} else {
					// No retired column and deletes are not allowed, i.e. we have no way to "save" the current state.
					throw new Exception('No retired column set for collection "' . $collectionName . '" (join table "' . $collection['join_table'] . '"). The `allow_deletes` attributes is also not set. Either add a retired column to ' . $collection['join_table'] . ' or enable deletes for this join table in the Cough model.');
				}
			}
		}

		/////////////////////////////////////////////////////////
		// Save all the added elements (update the join table)
		/////////////////////////////////////////////////////////

		/* 2007-02-19/AWB: This is now handled by the element itself.
		// First, build an array of value arrays to insert
		$values = array();
		foreach ($this->$collectionName->getAddedElements() as $elementID) {
			$values[] = array($elementID, $this->getKeyID());
		}

		// If we have some values to insert, insert them
		if ( ! empty($values)) {

			// What fields are we inserting?
			$fields = array($collection['collection_key'], $collection['relation_key']);

			// Do the insert
			$this->db->insertMultiple($collection['join_table'], $fields, $values);
		}
		*/
		
		$this->$collectionName->resetCollectionChanges();
	}
	
	protected function saveJoinFields() {
		
		if ($this->isJoinTableNew) {

			$this->setJoinField($this->getKeyName(), $this->getKeyID());
			$this->setJoinField($this->getCollector()->getKeyName(), $this->getCollector()->getKeyID());
			$this->db->doInsertOnDupUpdate($this->getJoinTableName(),$this->getJoinFields());
			
		} else if ($this->isJoinTableModified) {

			$this->setJoinField($this->getKeyName(), $this->getKeyID());
			$this->setJoinField($this->getCollector()->getKeyName(), $this->getCollector()->getKeyID());
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