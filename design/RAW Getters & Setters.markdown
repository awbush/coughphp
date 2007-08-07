
RAW Getters & Setters
=====================

Should we have them?

	/**
	 * Returns the specified object for use (raw get).
	 *
	 * @param $objectName - the name of the object to get
	 * @return CoughObject - the requested object
	 * @author Anthony Bush
	 **/
	protected function rawGetObject($objectName) {
		if ( ! $this->isObjectLoaded($objectName)) {
			$this->loadObject($objectName);
		}
		return $this->objects[$objectName];
	}
	
	/**
	 * Calls the get method for the given object name.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function getObject($objectName) {
		$getMethod = 'get' . self::titleCase($objectName) . '_Object';
		return $this->$getMethod();
	}
	
	/**
	 * Sets the object reference in memory (raw set).
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
	protected function rawSetObject($objectName, $object) {
		if (isset($this->objectDefinitions[$objectName])) {
			$this->objects[$objectName] = $object;
		}
	}
	
	/**
	 * Calls the set method for the given object name.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function setObject($objectName, $object) {
		$setMethod = 'set' . self::titleCase($objectName) . '_Object';
		$this->$setMethod($object);
	}
	
	/**
	 * Returns the current value of the requested field name.
	 *
	 * @return mixed
	 **/
	protected function rawGetField($fieldName) {
		if (isset($this->fields[$fieldName])) {
			return ($this->fields[$fieldName]);
		} else {
			return null;
		}
	}
	
	/**
	 * Calls the get method for the given field name.
	 *
	 * @return mixed
	 * @author Anthony Bush
	 **/
	public function getField($fieldName) {
		$getter = 'get' . self::titleCase($fieldName);
		return $this->$getter();
	}
	
	/**
	 * Sets the current value of $fieldName to $value.
	 * 
	 * @param string $fieldName
	 * @param mixed $value
	 * @return void
	 **/
	protected function rawSetField($fieldName, $value) {
		$this->setModifiedField($fieldName);
		$this->fields[$fieldName] = $value;
	}
	
	/**
	 * Calls the set method for the given field name.
	 *
	 * @return mixed
	 * @author Anthony Bush
	 **/
	public function setField($fieldName, $value) {
		$setter = 'set' . self::titleCase($fieldName);
		return $this->$setter($value);
	}
	