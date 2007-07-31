<?php

/**
 * undocumented abstract class CoughCollection
 * 
 * @package CoughPHP
 **/
abstract class CoughCollection extends ArrayObject {
	
	// Relationship managment
	const NONE = 0;
	const ONE_TO_MANY = 1;
	const MANY_TO_MANY = 2;
	protected $relationshipType = 0; // default to NONE
	protected $collector = null; // a reference to the collector of the collection, if any. Used in conjuction with relationshipType.
	protected $joinTableName = null;
	
	/**
	 * Whether or not {@link populateCollection} was run
	 *
	 * @var string
	 **/
	protected $populated = false;
	
	/**
	 * The database name to use to running queries.
	 * 
	 * Override in sub class.
	 * 
	 * @var string
	 **/
	protected $dbName = null;
	
	/**
	 * The collection SQL to use by default.
	 * 
	 * Override in sub class using defineCollectionSql
	 *
	 * @var string
	 **/
	protected $collectionSQL = '';
	
	/**
	 * The name of the element class that will be used when adding new
	 * elements to the collection (can be overridden when calling
	 * `populateCollection`).
	 * 
	 * Override this in sub class.
	 * 
	 * @var string
	 **/
	protected $elementClassName;
	
	/**
	 * The default ORDER BY clause to be used when populating the collection.
	 * 
	 * Override this in sub class using defineDefaultOrderClause()
	 * 
	 * @var string
	 **/
	protected $orderBySQL;
	
	/**
	 * Reference to database object.
	 *
	 * @var mixed
	 **/
	protected $db;
	
	/**
	 * Holds all the removed elements (unsaved)
	 *
	 * @var array of CoughObjects
	 **/
	protected $removedElements = array();
	
	public function getIterator() {
		return new CoughIterator( $this );
	}
	
	public function __construct($specialArgs=array(), $array=array()) {
		parent::__construct($array=array(), $flags=0, $iterator_class="CoughIterator");
		$this->initializeDefinitions($specialArgs);
		$this->db = DatabaseFactory::getDatabase($this->dbName);
	}
	
	protected function initializeDefinitions($specialArgs=array()) {
		$this->defineCollectionSql();
		$this->defineDefaultOrderClause();
		$this->defineSpecialCriteria($specialArgs);
	}
	
	/**
	 * Set custom SQL to be used when populating the collection.
	 * Override this in sub class.
	 *
	 * @return void
	 **/
	protected function defineCollectionSql() {
		$this->collectionSQL = '';
	}
	
	/**
	 * Set default ORDER BY clause to be used when populating the collection.
	 * Override this in sub class.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function defineDefaultOrderClause() {
		$this->orderBySQL = '';
	}
	
	/**
	 * Modify the collectionSQL based on special parameters.
	 * Override this in sub class.
	 *
	 * @return void
	 **/
	protected function defineSpecialCriteria($specialArgs=array()) {
		// this modifies the collectionSQL based on special parameters
	}
	
	/**
	 * Allows you to set the ORDER BY SQL after the Collection has been
	 * instantiated.
	 * 
	 * Example values:
	 * 
	 *     - "ORDER BY field_name ASC"
	 *     - "ORDER BY field_name ASC LIMIT 50 OFFSET 100"
	 * 
	 * @param string $orderBySQL - the SQL to use for ordering.
	 * @return void
	 * @author Anthony Bush
	 **/
	public function setOrderBy($orderBySQL) {
		$this->orderBySQL = $orderBySQL;
	}
	
	/**
	 * Populates the collection with optional overrides for both the element
	 * class name and the SQL (you can override either one, or both, or neither)
	 *
	 * TODO: $overrideOrderBySQL may be unnecessary, will be deprecated
	 * 
	 * @param string $overrideElementClassName - the element class name to instantiate for each collected item
	 * @param string $overrideSQL - the full SQL query to use
	 * @return void
	 * @author Anthony Bush
	 **/
	public function populateCollection($overrideElementClassName='', $overrideSQL='', $overrideOrderBySQL = null) {
		
		// Get collectionSQL
		if ( ! empty($overrideSQL)) {
			$collectionSQL = $overrideSQL;
		} else {
			$collectionSQL = $this->collectionSQL;
		}
		
		// Append ORDER BY SQL
		if ( ! is_null($overrideOrderBySQL)) {
			$collectionSQL .= ' ' . $overrideOrderBySQL;
		} else {
			$collectionSQL .= ' ' . $this->orderBySQL;
		}
		
		// Get collected element class name
		if ($overrideElementClassName != '') {
			$elementClassName = $overrideElementClassName;
		} else {
			$elementClassName = $this->elementClassName;
		}
		
		// Populate the collection
		
		$this->db->selectDb($this->dbName);
		$result = $this->db->query($collectionSQL);
		if ($result->numRows() > 0) {
			while ($row = $result->getRow()) {
				$this->add(new $elementClassName($row));
			}
		}
		
		$result->freeResult();
		$this->populated = true;
	}
	
	/**
	 * Returns whether or not the collection has been populated.
	 *
	 * @return boolean - true if the collection has been populated, false if not.
	 * @author Anthony Bush
	 **/
	public function isPopulated() {
		return $this->populated;
	}
	
	public function hasCollector() {
		if ($this->collector instanceof CoughObject) {
			return true;
		} else {
			return false;
		}
	}
	
	public function getCollector() {
		return $this->collector;
	}
	
	public function getJoinTableName() {
		return $this->joinTableName; 
	}
	
	public function setCollector($collector, $relationshipType, $joinTableName = null) {
		$this->collector = $collector;
		$this->relationshipType = $relationshipType;
		$this->joinTableName = $joinTableName;
	}
	
	public function isManyToManyCollection() {
		return $this->relationshipType == self::MANY_TO_MANY;
	}
	
	public function isOneToManyCollection() {
		return $this->relationshipType == self::ONE_TO_MANY;
	}
	
	/**
	 * Runs save on each collected element that is a CoughObject
	 * 
	 * @param array $fieldsToUpdate - an array of fields to be updated. Don't specify to have only changed fields updated.
	 * @return void
	 * @author Anthony Bush
	 **/
	public function save() {
		foreach ($this as $key => $element) {
			// Save the element, updating the collection's key if there wasn't one.
			if (!$element->hasKeyId()) {
				$element->save();
				$this->offsetUnset($key);
				$this->offsetSet($element->getKeyId(), $element);
			} else {
				$element->save();
			}
		}
		foreach ($this->removedElements as $element) {
			$element->save();
		}
		$this->removedElements = array();
	}
	
	/**
	 * Get the $n-th position in the array, regardless of key indices.
	 *
	 * $n = 0 gets first element, $n = (count - 1) gets last element.
	 *
	 * @param $n - which element to get (in range 0 to count - 1).
	 * @return mixed - nth element in array.
	 * @author Anthony Bush
	 **/
	public function getPosition($n) {
		$it = $this->getIterator();
		$count = $this->count();
		if ($count > $n) {
			$it->seek($n);
			return $it->current();
		} else {
			return null;
		}
	}
	
	/**
	 * Find out whether or not the collection is empty.
	 *
	 * @return boolean - true if nothing is in the collection, false otherwise.
	 * @author Anthony Bush
	 **/
	public function isEmpty() {
		if ($this->count() > 0) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Adds a single element given either an ID of the current collection
	 * object type to be added or the object itself.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function add($objectOrID, $joinFields = null) {
		if ( ! ($objectOrID instanceof CoughObject)) {
			// It's an id, not an object.
			$elementClassName = $this->elementClassName;
			$object = new $elementClassName($objectOrID);
		} else {
			$object = $objectOrID;
		}
		
		// We have the object...
		$object->setCollector($this->getCollector());
		if ($this->isManyToManyCollection()) {
			$object->setJoinTableName($this->getJoinTableName());
			$object->setJoinFields($joinFields);
			$object->setIsJoinTableNew(true);
		}
		
		// Add the object to the collection
		if ($object->hasKeyId()) {
			$this->offsetSet($object->getKeyId(), $object);
		} else {
			$this->append($object);
		}
	}
	
	/**
	 * Removes a single element given either an ID of the current collection
	 * object type to be removed or the object itself.
	 *
	 * @return CoughObject - the element that was removed
	 * @author Anthony Bush
	 **/
	public function remove($objectOrId) {
		if (is_object($objectOrId)) {
			if ($objectOrId->hasKeyId()) {
				return $this->removeByKey($objectOrId->getKeyId());
			} else {
				return $this->removeByReference($objectOrId);
			}
		} else {
			return $this->removeByKey($objectOrId);
		}
	}
	
	protected function removeByKey($key) {
		if ($this->offsetExists($key)) {
			$objectToRemove = $this->offsetGet($key);
			$this->offsetUnset($key);
			$this->removedElements[] = $objectToRemove;
			$objectToRemove->setCollector(null);
			return $objectToRemove;
		}
		return false;
	}
	
	protected function removeByReference($objectToRemove) {
		foreach ($this as $key => $element) {
			if ($element == $objectToRemove) {
				$this->offsetUnset($key);
				$this->removedElements[] = $objectToRemove;
				$objectToRemove->setCollector(null);
				return $objectToRemove;
			}
		}
		return false;
	}
	
	/**
	 * Sort the collection after population via the return value of the
	 * specified method name of the collected objects.
	 * 
	 * For example:
	 * 
	 *    $pc = new woc_Product_Collection();
	 *    $pc->populateCollection();
	 *    $pc->sortBy('getName'); // equal to $pc->sortBy('getName', 'a');
	 *    // or
	 *    $pc->sortyBy('getName', 'd') // sorts descending
	 * 
	 * TODO: Future functionality could allow an array of objectMethodNames for
	 * the first parameter allowing a multi-sort option.
	 * 
	 * @param string $objectMethodName - the method name of the collected objects to use for get a value to sort against
	 * @param string $direction - 'a' or 'd' for ascending or descending
	 * @return void
	 * @author Anthony Bush
	 **/
	public function sortBy($objectMethodName, $direction = 'a') {
		
		// Step 1: For each collected element, copy the key and value of the
		// given object method name into a temp array.
		$sortMe = array();
		$it = $this->getIterator();
		while ($it->valid()) {
			$sortMe[$it->key()] = $it->current()->$objectMethodName();
			$it->next();
		}
		
		// Step 2: Sort the array by the value from the object method name
		switch (strtolower($direction[0])) {
			case 'd':
				arsort($sortMe);
			break;
			
			case 'a':
			default:
				asort($sortMe);
			break;
		}
		
		// Step 3: Get a copy of the sorted collection with objects (key => obj)
		$sorted = array();
		foreach (array_keys($sortMe) as $key) {
			$sorted[$key] = $this->offsetGet($key);
		}
		
		// Step 4: Setup the collection with the new sorted array
		$this->exchangeArray($sorted);
		
	}
	
}


?>