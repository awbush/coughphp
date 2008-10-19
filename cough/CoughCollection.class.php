<?php

/**
 * CoughCollection collects CoughObjects.
 * 
 * @package cough
 **/
abstract class CoughCollection extends ArrayObject {
	
	/**
	 * The name of the element class that will be used when adding new
	 * elements to the collection
	 * 
	 * Override this in sub class.
	 * 
	 * @var string
	 **/
	protected $elementClassName;
	
	/**
	 * Holds all the (unsaved) removed elements
	 *
	 * @var array of CoughObjects
	 **/
	protected $removedElements = array();
	
	public function __construct() {
		parent::__construct(array(), 0, 'CoughIterator');
	}
	
	/**
	 * Returns a new iterator for the collection.
	 *
	 * @return CoughIterator
	 **/
	public function getIterator() {
		return new CoughIterator($this);
	}
	
	/**
	 * Returns a new key-value iterator for the collection.
	 *
	 * @return CoughKeyValueIterator
	 **/
	public function getKeyValueIterator($value, $key = 'getKeyId') {
		return new CoughKeyValueIterator($this, $value, $key);
	}
	
	/**
	 * Returns a reference to the database object to use for queries.
	 *
	 * @return CoughAbstractDatabaseAdapter
	 **/
	public function getDb() {
		return call_user_func(array($this->elementClassName, 'getDb'));
	}
	
	/**
	 * Returns the base SQL to use for the collection.
	 * 
	 * Defaults to just calling the object's {@link getLoadSql()} static method.
	 * 
	 * Override in sub class to use different SQL.
	 *
	 * @return mixed return value of CoughObject::getLoadSql()
	 **/
	public function getLoadSql() {
		return call_user_func(array($this->elementClassName, 'getLoadSql'));
	}
	
	/**
	 * Loads the collection using the SQL provided by {@link getLoadSql()}
	 *
	 * @return void
	 **/
	public function load() {
		$this->loadBySql($this->getLoadSql());
	}
	
	/**
	 * Loads the collection using the provided SQL
	 *
	 * @return void
	 **/
	public function loadBySql($sql) {
		$elementClassName = $this->elementClassName;
		$db = $this->getDb();
		$db->selectDb(call_user_func(array($elementClassName, 'getDbName')));
		$result = $db->query($sql);
		if ($result->getNumRows() > 0) {
			while ($row = $result->getRow()) {
				$this->add(call_user_func(array($elementClassName, 'constructByFields'), $row));
			}
		}
	}
	
	/**
	 * Run save on each collected (or removed) element.
	 * 
	 * @return void
	 * @author Anthony Bush
	 **/
	public function save() {
		$temporaryKeys = array();
		
		// Save all elements and keep track of temporary keys so that we can update them.
		foreach ($this as $key => $element) {
			if (!$element->hasKeyId()) {
				$temporaryKeys[$key] = $element;
			}
			$element->save();
		}
		
		// Update the temporary keys
		foreach ($temporaryKeys as $key => $element) {
			$this->offsetUnset($key);
			$this->offsetSet($element->getKeyId(), $element);
		}
		
		// Save all the removed items
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
	 * @param int $n which element to get (in range 0 to count - 1).
	 * @return mixed nth element in array.
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
	 * The first element in the array/collection.
	 *
	 * @return CoughObject|null null if collection is empty
	 * @author Anthony Bush
	 * @since 2008-10-08
	 **/
	public function getFirst() {
		return $this->getPosition(0);
	}
	
	/**
	 * The last element in the array/collection.
	 *
	 * @return CoughObject|null null if collection is empty
	 * @author Anthony Bush
	 * @since 2008-10-08
	 **/
	public function getLast() {
		return $this->getPosition($this->count() - 1);
	}
	
	/**
	 * Find out whether or not the collection is empty.
	 *
	 * @return boolean true if nothing is in the collection, false otherwise.
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
	 * Returns the element at the given key.
	 * 
	 * @param mixed $key the result from the element's {@link CoughObject::getKeyId()}
	 * method (which always returns a flattened string/integer) or the result from
	 * the element's {@link CoughObject::getPk()} method (which always returns the
	 * array form of the key).
	 * @return mixed the CoughObject if found, null if not.
	 * @author Anthony Bush
	 **/
	public function get($key) {
		if (is_array($key)) {
			$key = implode(',', $key);
		}
		if (!is_null($key) && isset($this[$key])) { // don't use offsetExists b/c it doesn't work in PHP <= 5.2.1
			return $this->offsetGet($key);
		} else {
			return null;
		}
	}
	
	/**
	 * Adds a single element (even if it doesn't have a key ID yet).
	 * 
	 * @param CoughObject $object
	 * @return void
	 * @author Anthony Bush
	 **/
	public function add(CoughObject $object) {
		if ($object->hasKeyId()) {
			$this->offsetSet($object->getKeyId(), $object);
		} else {
			$this->offsetSet(CoughCollection::getTemporaryKey(), $object);
		}
	}
	
	/**
	 * Returns a temporary key for adding an object that does not have a key ID
	 * yet.
	 *
	 * @return string
	 * @author Anthony Bush
	 **/
	protected static function getTemporaryKey() {
		static $count = 0;
		++$count;
		return 'NULL_KEY_' . $count;
	}
	
	/**
	 * Removes a single element given either an ID or the object itself.
	 * 
	 * @param CoughObject|int|string $objectOrId object or ID to remove (ID must be result from $object->getKeyId()).
	 * @return mixed CoughObject that was removed, or false if no element could be found/removed.
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
	
	/**
	 * Removes a single element from the collection by key.
	 * 
	 * Due to a bug in PHP 5.2.1 and earlier, we do not use offsetExists()...
	 * This has the drawback of not working when a value exists, but is null,
	 * but if that ever happens behavior is UNDEFINED anyway (i.e. how do you have
	 * a null element in a CoughCollection? It makes no sense.)
	 * See http://bugs.php.net/bug.php?id=40872 for more details.
	 *
	 * @return mixed CoughObject that was removed, or false if no element could be found/removed.
	 * @author Anthony Bush
	 **/
	protected function removeByKey($key) {
		if (isset($this[$key])) {
			$objectToRemove = $this->offsetGet($key);
			$this->offsetUnset($key);
			$this->removedElements[] = $objectToRemove;
			return $objectToRemove;
		}
		return false;
	}
	
	/**
	 * Removes a single element from the collection by comparing references.
	 *
	 * @return mixed CoughObject that was removed, or false if no element could be found/removed.
	 * @author Anthony Bush
	 **/
	protected function removeByReference($objectToRemove) {
		foreach ($this as $key => $element) {
			if ($element == $objectToRemove) {
				$this->offsetUnset($key);
				$this->removedElements[] = $objectToRemove;
				return $objectToRemove;
			}
		}
		return false;
	}
	
	/**
	 * Sort the collection from an array of keys (where the keys are in the
	 * desired order).
	 * 
	 * @param array $keys ordered key IDs for the elements in the collection.
	 * @return void
	 * @author Anthony Bush
	 **/
	public function sortByKeys($keys) {
		$sorted = array();
		foreach ($keys as $key) {
			$sorted[$key] = $this->offsetGet($key);
		}
		$this->exchangeArray($sorted);
	}
	
	/**
	 * Sort the collection from the return value of the specified method name of the
	 * collected objects.
	 * 
	 * Examples:
	 * 
	 *     <code>
	 *     $collection->sortByMethod('getProductName');
	 *     $collection->sortByMethod('getProductName', SORT_ASC);
	 *     $collection->sortByMethod('getProductName', SORT_DESC);
	 *     </code>
	 * 
	 * @param string $methodName
	 * @param int $direction SORT_ASC or SORT_DESC (PHP sort order constant)
	 * @return void
	 * @author Anthony Bush
	 **/
	public function sortByMethod($methodName, $direction = SORT_ASC) {
		$sortMe = array();
		foreach ($this as $key => $element) {
			$sortMe[$key] = $element->$methodName();
		}
		switch ($direction) {
			case SORT_DESC:
				arsort($sortMe);
			break;
			case SORT_ASC:
			default:
				asort($sortMe);
			break;
		}
		$this->sortByKeys(array_keys($sortMe));
	}
	
	/**
	 * Sort the collection from the return value of the specified method names of the
	 * collected objects.
	 * 
	 * Examples:
	 * 
	 *     <code>
	 *     $collection->sortByMethods('getManufacturerName', 'getProductName');
	 *     $collection->sortByMethods('getManufacturerName', SORT_DESC, 'getProductName', SORT_DESC);
	 *     $collection->sortByMethods('getManufacturerName', SORT_DESC, 'getProductName', SORT_ASC);
	 *     $collection->sortByMethods('getManufacturerName', SORT_DESC, SORT_STRING, 'getProductName', SORT_STRING);
	 *     </code>
	 * 
	 * @param string $methodName
	 * @param int $direction SORT_ASC or SORT_DESC (PHP sort order constant)
	 * @param int $type SORT_REGULAR, SORT_STRING, or SORT_NUMERIC (PHP sort type constant)
	 * @return void
	 * @see http://php.net/array_multisort
	 * @author Anthony Bush
	 **/
	public function sortByMethods() {
		$multisortArgs = func_get_args();
		$keyValueArrays = array();
		
		if (empty($multisortArgs))
		{
			throw new Exception('missing parameter in sortByMethods');
		}
		
		// Build multisortArgs with references to the key value pair arrays to do the sorting on.
		foreach ($multisortArgs as $argIndex => $arg) {
			if (!is_int($arg)) {
				// NOTE: We add the 'A' b/c we don't want array_multisort to re-index "numeric" keys.
				$keyValueArrays[$argIndex] = array();
				foreach ($this as $key => $element) {
					$keyValueArrays[$argIndex]['A' . $key] = $element->$arg();
				}
				// Have to store references to the key value pairs for call_user_func_array to work with array_multisort.
				$multisortArgs[$argIndex] = &$keyValueArrays[$argIndex];
			}
		}
		
		// Sort
		$success = call_user_func_array('array_multisort', $multisortArgs);
		
		if ($success)
		{
			// Update the collection
			// NOTE: Can't use sortByKeys() b/c we have to strip the 'A' hack from above.
			$sorted = array();
			foreach (array_keys($multisortArgs[0]) as $key) {
				$key = substr($key, 1);
				$sorted[$key] = $this->offsetGet($key);
			}
			$this->exchangeArray($sorted);
		}
	}
	
}

?>
