<?php

/**
 * undocumented abstract class CoughCollection
 * 
 * @package CoughPHP
 **/
abstract class CoughCollection extends ArrayObject {
	// Values for the collectionType;
	const KEYLESS = 0;
	const KEYED   = 1;
	protected $collectionType;
	
	// Changed status of each collected object
	const REMOVED = 0;
	const ADDED = 1;
	const NOCHANGE = 2;
	protected $collectionChanges = array();
	protected $trackingEnabled = true;
	
	// Relationship managment
	const NONE = 0;
	const ONE_TO_MANY = 1;
	const MANY_TO_MANY = 2;
	protected $relationshipType = 0; // default to NONE
	protected $collector = null; // a reference to the collector of the collection, if any. Used in conjuction with relationshipType.
	protected $joinTableName = null;
	
	protected $populated = false;
	
	protected $dbName;
	protected $collectionSQL;
	protected $elementClassName;
	protected $orderBySQL;
	
	protected $init_dbName = '';
	protected $init_collectionSQL = '';
	protected $init_elementClassName = '';
	protected $init_orderBySQL = '';
	
	/**
	 * Reference to database object.
	 *
	 * @var mixed
	 **/
	protected $db;
	
	public function getIterator() {
		return new CoughIterator( $this );
	}
	
	public function __construct($specialArgs=array(), $array=array()) {
		parent::__construct($array=array(), $flags=0, $iterator_class="CoughIterator");
		$this->initializeDefinitions($specialArgs);
		$this->db = DatabaseFactory::getDatabase($this->dbName);
	}
	protected function initializeDefinitions($specialArgs=array()) {
		$this->defineDBName();
		$this->defineCollectionSQL();
		$this->defineCollectionType();
		$this->defineDefaultOrderClause();
		$this->defineElementClassName();
		$this->defineSpecialCriteria($specialArgs);
	}
	/**
	 * Set the database name of the collection.
	 * Override this in sub class.
	 *
	 * @return void
	 **/
	protected function defineDBName() {
		$this->dbName = '';
	}
	/**
	 * Set custom SQL to be used when populating the collection.
	 * Override this in sub class.
	 *
	 * @return void
	 **/
	protected function defineCollectionSQL() {
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
	 * Set the collection type of the collection, KEYED, or KEYLESS.
	 * Override this in sub class.
	 * 
	 * TODO: Cough: Remove KEYED support and move to KEYLESS mode full time?
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function defineCollectionType() {
		$this->collectionType = self::KEYED;
	}
	/**
	 * Set the name of the element class that will be used when adding new
	 * elements to the collection (can be overridden when calling
	 * `populateCollection`).
	 * Override this in sub class.
	 *
	 * @return void
	 **/
	protected function defineElementClassName() {
		$this->elementClassName = '';
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
	 * Alias of populateCollection with no parameters.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function check() {
		$this->populateCollection();
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
		$result = $this->db->doQuery($collectionSQL);
		if ($result->numRows() > 0) {
			// Disable tracking for these elements that are fresh from the database
			$this->trackingEnabled = false;
			while ($row = $result->getRow()) {
				$this->addElement(new $elementClassName($row));
			}
			$this->trackingEnabled = true;
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
	public function save($fieldsToUpdate = null) {
		$it = $this->getIterator();
		while ($it->valid()) {
			$element = $it->current();
			if ($element instanceof CoughObject) {
				$element->save($fieldsToUpdate);
			}
			$it->next();
		}
	}
	
	/**
	 * Returns an array containing the keys of all collected objects
	 *
	 * @return array - array containing the keys of all collected objects
	 * @author Wayne Wight
	 **/
	public function getKeys() {
		$keys = array();
		$it = $this->getIterator();
		while($it->valid()) {
			$collected = $it->current();
			$keys[] = $collected->getKeyID();
			$it->next();
		}
		return $keys;
	}
	
	/**
	 * Returns a hash containing the key => name values of all collected objects
	 *
	 * @return array - hash containing the key => name values of all collected objects
	 * @author Wayne Wight
	 **/
	public function getHash() {
		$hash = array();
		$it = $this->getIterator();
		while($it->valid()) {
			$collected = $it->current();
			$hash[$collected->getKeyID()] = $collected->getName();
			$it->next();
		}
		return $hash;
	}
	
	/**
	 * Returns the first element in the collection, or null if nothing in collection.
	 *
	 * @return mixed - first element in the collection, or null if nothing in collection.
	 * @author Anthony Bush
	 **/
	public function getFirst() {
		$it = $this->getIterator();
		$it->rewind();
		if ($it->valid()) {
			return $it->current();
		} else {
			return null;
		}
	}
	
	/**
	 * Returns the last element in the collection, or null if nothing in collection.
	 *
	 * @return mixed - last element in the collection, or null if nothing in collection.
	 * @author Anthony Bush
	 **/
	public function getLast() {
		$it = $this->getIterator();
		$count = $this->count();
		if ($count > 0) {
			$it->seek($count - 1);
			return $it->current();
		} else {
			return null;
		}
	}
	
	/**
	 * Returns a random element from the collection.
	 *
	 * @return mixed - a random element from the collection, or null if nothing in collection.
	 * @author Anthony Bush
	 **/
	public function getRandom() {
		$it = $this->getIterator();
		$count = $this->count();
		if ($count > 0) {
			$it->seek(rand(0, $count - 1));
			return $it->current();
		} else {
			return null;
		}
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
	 * Set the collection to either:
	 *  - another collection
	 *  - or an array of elements
	 *  - or a single element
	 * 
	 * where each element is either an ID of the current collection object type
	 * to be added or the object itself.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function set($objectsOrIDs) {
		$this->removeAll();
		$this->add($objectsOrIDs);
	}
	
	/**
	 * Calls the appropriate addCollection() or addElements() function based
	 * on the the type of object passed in.
	 * 
	 * If you know ahead of time what type you are adding, call the appropriate
	 * function yourself rather than calling this.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function add($objectsOrIDs, $joinFields = null) {
		if (get_class($objectsOrIDs) && is_subclass_of($objectsOrIDs,'CoughCollection')) {
			$this->addCollection($objectsOrIDs);
		} else {
			$this->addElements($objectsOrIDs, $joinFields);
		}
	}
	
	/**
	 * Adds all the elements of the collection object to the existing
	 * collection.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function addCollection($collectionObj) {
		foreach ($collectionObj as $elementObj) {
			$this->addElement($elementObj);
		}
	}
	
	/**
	 * Adds a single element or an array of elements, where each element is
	 * either an ID of the current collection object type to be added or the
	 * object itself.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function addElements($objectsOrIDs, $joinFields = null) {
		if (is_array($objectsOrIDs)) {
			// An array of objects or IDs
			foreach ($objectsOrIDs as $objectOrID) {
				$this->addElement($objectOrID, $joinFields);
			}
		} else if ( ! empty($objectsOrIDs)) {
			// One object or ID
			$this->addElement($objectsOrIDs, $joinFields);
		}
	}
	
	/**
	 * Adds a single element given either an ID of the current collection
	 * object type to be added or the object itself.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function addElement($objectOrID, $joinFields = null) {
		if ( ! ($objectOrID instanceof CoughObject)) {
			// It's an id, not an object.
			$elementClassName = $this->elementClassName;
			$objectOrID = new $elementClassName($objectOrID);
		}
		
		// We have the object...
		$objectOrID->setCollector($this->getCollector());
		if ($this->isManyToManyCollection()) {
			$objectOrID->setJoinTableName($this->getJoinTableName());
			$objectOrID->setJoinFields($joinFields);
			$objectOrID->setIsJoinTableNew(true);
		}
		
		// Add the object to the collection
		$object = $objectOrID;
		$key = $objectOrID->getKeyID();
		if ($this->collectionType == self::KEYLESS || $key === null) {
			$this->append($object);
			
			// TODO: How do we keep track of the above change? Can we get the
			// offset of where the value was appended? It's possible that we
			// don't care. If you use the object's adding functions, then it
			// can keep track of whether or not it is changed so it will know
			// to call save() based on that. And, since save() saves all
			// indiscriminently of whether or not internal modifications have
			// been made, there is not much use in keeping track of changes here.
		} else {
			if ( ! $this->offsetExists($key)) {
				$this->offsetSet($key, $object);
				$this->trackAdd($key);
			}
		}
	}
	
	/**
	 * Calls the appropriate removeCollection() or removeElements() function
	 * based on the the type of object passed in.
	 * 
	 * If you know ahead of time what type you are removing, call the
	 * appropriate function yourself rather than calling this.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function remove($objectsOrIDs) {
		if (get_class($objectsOrIDs) && is_subclass_of($objectsOrIDs,'CoughCollection')) {
			$this->removeCollection($objectsOrIDs);
		} else {
			$this->removeElements($objectsOrIDs);
		}
	}
	
	/**
	 * Removes all the elements of the given collecton object from the existing
	 * collection.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function removeCollection($collectionObj) {
		foreach ($collectionObj as $elementObj) {
			$this->removeElement($elementObj);
		}
	}
	
	/**
	 * Removes a single element or an array of elements, where each element is
	 * either an ID of the current collection object type to be removed or the
	 * object itself.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function removeElements($objectsOrIDs) {
		if (is_array($objectsOrIDs)) {
			// An array of objects or IDs
			foreach ($objectsOrIDs as $objectOrID) {
				$this->removeElement($objectOrID);
			}
		} else if ( ! empty($objectsOrIDs)) {
			// One object or ID
			$this->removeElement($objectsOrIDs);
		}
	}
	
	/**
	 * Removes a single element given either an ID of the current collection
	 * object type to be removed or the object itself.
	 *
	 * @return boolean - true if the element was removed, false if not
	 * @author Anthony Bush
	 **/
	public function removeElement($objectOrID) {
		if (get_class($objectOrID) && is_subclass_of($objectOrID, 'CoughObject')) {
			// It's an object
			$key = $objectOrID->getKeyID();
		} else {
			// It's an id
			$key = $objectOrID;
		}
		if ($this->collectionType == self::KEYED && $this->offsetExists($key)) {
			$this->offsetUnset($key);
			$this->trackRemove($key);
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Removes all elements from the Collection.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function removeAll() {
		// Keep track of the items we are about to remove
		if ($this->collectionType == self::KEYED) {
			$it = $this->getIterator();
			while ($it->valid()) {
				$this->trackRemove($it->key());
				$it->next();
			}
		}
		
		// Remove them all
		$this->exchangeArray(array());
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
	
	
	#######################
	# Collection Statuses #
	#######################

	
	/**
	 * Tells whether or not an item in the collection has been added or
	 * removed. It does not check to see if the individual items have
	 * been modified.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function isModified() {
		foreach ($this->collectionChanges as $changedID => $changedStatus) {
			if ($changedStatus != self::NOCHANGE) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Resets the status of what has been changed. Meant to be called after
	 * a sync to the database.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function resetCollectionChanges() {
		$this->collectionChanges = array();
	}
	
	/**
	 * Returns an array of KeyIDs that have been removed in comparision to what
	 * was populated.
	 *
	 * @return array - element IDs for elements that have been removed.
	 * @author Anthony Bush
	 **/
	public function getRemovedElements() {
		$removedElements = array();
		foreach ($this->collectionChanges as $changedID => $changedStatus) {
			if ($changedStatus == self::REMOVED) {
				$removedElements[] = $changedID;
			}
		}
		return $removedElements;
	}
	
	/**
	 * Returns an array of KeyIDs that have been added in comparision to what
	 * was populated.
	 *
	 * @return array - element IDs for elements that have been added.
	 * @author Anthony Bush
	 **/
	public function getAddedElements() {
		$addedElements = array();
		foreach ($this->collectionChanges as $changedID => $changedStatus) {
			if ($changedStatus == self::ADDED) {
				$addedElements[] = $changedID;
			}
		}
		return $addedElements;
	}
	
	/**
	 * Tracks the remove of an item from the collection.
	 *
	 * @return string $key - the element ID or key that has been removed.
	 * @author Anthony Bush
	 **/
	protected function trackRemove($key) {
		if (!$this->trackingEnabled) {
			return;
		}
		if (isset($this->collectionChanges[$key]) && $this->collectionChanges[$key] == self::ADDED) {
			$this->collectionChanges[$key] = self::NOCHANGE;
		} else {
			$this->collectionChanges[$key] = self::REMOVED;
		}
	}

	/**
	 * Tracks the addition of an item to the collection.
	 *
	 * @return string $key - the element ID or key that has been added.
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function trackAdd($key) {
		if (!$this->trackingEnabled) {
			return;
		}
		if (isset($this->collectionChanges[$key]) && $this->collectionChanges[$key] == self::REMOVED) {
			$this->collectionChanges[$key] = self::NOCHANGE;
		} else {
			$this->collectionChanges[$key] = self::ADDED;
		}
	}
	
}


?>
