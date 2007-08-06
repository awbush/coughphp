Static Methods and Single-Query Initialization
==============================================


Static Methods
--------------

<?php

interface CoughFactoryInterface {
	// are we including this? it could check the data passed in and try to call the appropriate construct method below
	public static function construct($stuff);
	
	// name implies you can construct by primary key or by a unique key (using a hash so it knows what column names to look up)
	public static function constructByKey($key);
	
	// typically this one would be called by constructByKey
	public static function constructByCriteria($criteria);
	
	// typically this one would be called by constructByCriteria
	public static function constructBySql($sql);
	
	// typically this one would be called by constructBySql or end-user or relationships (collections and one-to-one objects)
	public static function constructByFields($fields);
}

class ConcreteClass implements CoughFactoryInterface {
	protected static $dbName = 'content';
	protected static $tableName = 'cms_component';
	protected static $pkFieldNames = array('component_id');
	public static function construct() {
		echo 'ConcreteClass::construct()' . "\n";
	}
}

?>


Sample Generator Code
---------------------

Some of the static methods might be generated like the following...

	// Static Construction Methods
	
	/**
	 * Constructs a new <?php echo $starterObjectClassName ?> object from
	 * a single id (for single key PKs) or a hash of [field_name] => [field_value].
	 * 
	 * The key is used to pull data from the database, and, if no data is found,
	 * null is returned. You can use this function with any unique keys or the
	 * primary key as long as a hash is used.
	 * 
	 * @return <?php echo $starterObjectClassName ?>
	 **/
	public static function constructByKey($idOrIdArray) {
		if (is_array($idOrIdArray)) {
			$fields = $idOrIdArray;
		} else {
			$fields = array();
			foreach (self::getPkFieldNames() as $fieldName) {
				$fields[$fieldName] = $idOrIdArray;
			}
		}
		$db = <?php echo $starterObjectClassName ?>::getDb();
		$sql = <?php echo $starterObjectClassName ?>::getLoadSqlWithoutWhere() . ' ' . $db->generateWhere($fields);
		return self::constructBySql($sql);
		$result = $db->query($sql);
		if ($row = $result->getRow()) {
			return self::constructByFields($row);
		} else {
			return null;
		}
	}
	
	public static function constructBySql($sql) {
		if ( ! empty($sql)) {
			$db = <?php echo $starterObjectClassName ?>::getDb();
			$db->selectDb(self::$dbName);
			$result = $db->query($sql);
			if ($result->numRows() == 1) {
				return self::constructByFields($result->getRow());
			}
			$result->freeResult();
		}
		return null;
	}
	
	// potential temporary implementation before we have all the DB methods static...
	public static function constructByKey($idOrIdArray) {
		if (is_array($idOrIdArray)) {
			$object = new <?php echo $starterObjectClassName ?>($idOrIdArray);
			$object->load();
		} else {
			$object = new <?php echo $starterObjectClassName ?>($idOrIdArray);
		}
		if ($object->isLoaded()) {
			return $object;
		} else {
			return null;
		}
	}
	
	/**
	 * Constructs a new <?php echo $starterObjectClassName ?> object after
	 * checking the fields array to make sure the appropriate subclass is
	 * used.
	 * 
	 * No queries are run against the database.
	 * 
	 * @return <?php echo $starterObjectClassName ?>
	 **/
	public static function constructByFields($hash) {
		return new <?php echo $starterObjectClassName ?>($hash);
		// Would we need isLoaded status when using static methods? $object->setIsLoaded(true);
	}
	


Single-Query Initialization
---------------------------

The problem with where this is going is that the dbName, tableName, and pkFieldNames are all static but the fields, object definitions, and collection definitions are not. (so that they can be appended to in sub classes rather than copied, pasted and appended).

What I'd like to do is table the idea of the static-ness and instead address the single-query initialization we desire. I feel it may provide insight as to a better way to do the static-ness.  For example, if we want to pull both an order and a customer account with one query, we should be able to pass that data in to the constructor and have it properly call the correct initialization for the one-to-one objects.

	SELECT
		order.*,
		customer.*
	FROM
		order
		INNER JOIN customer USING (customer_id)
	WHERE
		order_id = 123

One problem is that we have to alias the fields in at least the customer table in order to avoid column name conflicts, and we have to wonder where does that information come from? We can, as the Cough user, put that data into the query hard coded, but we already have the field list via the generated customer object (which could be available through a static interface...), so we hate to duplicate it because we always want to pull all values (i.e. the same result as just pulling the order with one query, and then the customer with a second query via $order->getCustomer()).

Let's list the steps that need to be handled:

1. Fields need to pulled from somewhere (this happens in the check/load methods)
	* Either hand written out (bad for maintainability, slow and tedious), or
	* Ask the model in question what it's fields are (Customer model in this case) -- this is one route where the static field definitions would help because then we wouldn't need to instantiate the customer object just to ask it what its fields are, or
	* Ask some other shared repository of information that keeps track of the schema.

2. Database result row needs to be properly parsed back out (explode on period in the field names). (this happens in the check/load methods)
	* Make the database abstraction layer do it, or
	* Add some internal methods to handle it (perhaps static method on CoughObject? the logic won't change, and no need to have tons of copies of the function in memory...)

3. Parsed out fields need to be properly handled (this happens in the initialization / __construct method). For example, while processing initialization data we should check if the value is an array, and if it is assume the key is the entity name and the array is the data for that entity. Be careful of naming conflicts where a column on the primary table is the same as the table you are joining too; we can avoid this issue by also requiring all field names for the primary table to be aliased as well.

	foreach ($fields as $key => $value) {
		if (is_array($value)) {
			if (isset($this->objectDefinitions[$key])) {
				$className = $this->objectDefinitions[$key]['class_name'];
				$object = $className::constructByFields($value);
				$this->setObject($key, $object);
			} else if (isset($this->collectionDefinitions[$key])) {
				$className = $this->collectionDefinitions[$key]['class_name'];
				$object = $className::constructByFields($value); // <- what's the constructor method name for collections?
					// when it's constructed like the above it is assumed that it can just loop through the value you gave it and call the same static method on it's children, e.g.
					$collection = new <collection_name>(); // Hmm, do collections need the same factory type switching ability elements have? How would we address a collection containing a mixture? I'm of the opinion the collection holds the elements of the superclass type, e.g. it only cares that Order and Quote extend Ticket and thus would be called a Ticket collection. Like-wise a TicketLine collection might hold any of the line type objects, so long as they at least extend TicketLine. So no, the collection doesn't need the type switching factory, which makes this doable.
					$elementName = self::$elementClass;
					foreach ($hash as $key => $elementFields) {
						$element = $elementName::consructByFields($elementFields);
						if (!is_null($element)) {
							$this->add($element);
						}
					}
				$this->setCollection($key, $object); // not to be confused with getCollection($key)->set($data)?
			}
		} else {
			$this->setField($key, $value);
		}
	}
	
	// should this method on CoughObject take both an object reference and a hash or require that you use the hash to build an object yourself?
	public function setObject($objectName, $objectOrHash) {
		if (is_array($objectOrHash) {
			if (isset($this->objectDefinitions[$objectName])) {
				$className = $this->objectDefinitions[$objectName]['class_name'];
				$object = $className::constructByFields($objectOrHash);
				$this->objects[$objectName] = $object;
			}
		} else {
			$this->objects[$objectName] = $objectOrHash;
		}
	}
