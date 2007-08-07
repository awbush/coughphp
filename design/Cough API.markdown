
Cough API
=========

Definitions
Construction

Definitions
-----------

The minimal data set that must be defined is the database name, table name, fields, and primary key:

	class ConcreteCoughObject extends CoughObject {
		protected $dbName = 'db_name';
		protected $tableName = 'table_name';
		protected $fields = array(
			'field1' => 'field1_default_value',
			'field2' => 'field2_default_value',
			'field3' => 'field3_default_value',
			// ...
			'fieldn' => 'fieldn_default_value'
		);
		protected $pkFieldNames = array('field1'); // multi-key PK example: array('field1','field2');
	}

Optional definitions include object and collection definitions:

	class ConcreteCoughObject extends CoughObject {
		protected $objectDefinitions = array(); // hash of [object_name] => [object definition]
		protected $collectionDefinitions = array(); // hash of [object_name] => [object definition]
	}

CoughObject calls a `initDefinitions()` function in the constructor which calls the following methods that may be used to override any of the above definitions:

	protected function initDefinitions() {
		$this->defineObjects();
		$this->defineCollections();
	}

If there is a need to populate object definitions by appending to the definitions that are already present, it is preferred to do it in `defineObjects` or `defineCollections` like so:

	protected function defineObjects() {
		parent::defineObjects();
		$this->objectDefinitions['object_name'] = array(); // fill in the array with the definitions
	}
	protected function defineCollections() {
		parent::defineCollections();
		$this->collectionDefinitions['collection_name'] = array(); // fill in the array with the definitions
	}

If needing to change the database name, table name or fields / default field values, just set them at the member variable level. If needing to do it at run-time, do it in the `initDefinitions()` method like so:

	protected function initDefinitions() {
		parent::initDefinitions();
		$possibleDbs = array('database1','database2');
		$dbIndex = array_rand($possibleDatabases);
		$this->dbName = $possibleDatabases[$dbIndex];
	}

Construction
------------

Construction of a CoughObject can be done in a few ways:

Using a single value that is the primary key id of the object:

	$object = new Object($id);
	$object = Object::construct($id); // TODO: if ID can not be found, should NULL be returned or an empty object?

The above methods will initialize the key ID and attempt to look up their related data in the database. If that data is already available, the following methods will work.

Using pre-existing data in array form (format of [field_name] => [field_value]):

	$object = new Object($hash);
	$object = Object::construct($hash);

The `construct()` static method will call one of two other static methods:

	public static function constructByPk($idOrIdArray) {
		if (is_array($idOrIdArray)) {
			$object = new Object($idOrIdArray);
			$object->load();
		} else {
			$object = new Object($idOrIdArray);
		}
		return $object; // TODO if load gets no data, should we return NULL?
	}

If you have a multi-key primary key, then you will have to use the `constructByPk()` method.

	public static function constructByFields($hash) {
		return new Order($hash);
	}


### Example of overriding the static method (TODO: Moved to advanced section?) ###

In this static method example, either an Order or a Quote object is returned.

	$ticket = Ticket::construct($hash);

The above example might work with an overridden `constructByFields` method (which `construct` will call), defined as follows:

	class Ticket extends CoughObject {
		const TYPE_QUOTE = 1;
		const TYPE_ORDER = 2;
		public static function constructByFields($fields) {
			switch ($fields['ticket_type_id']) {
				case Ticket::TYPE_QUOTE:
					return new Quote($fields);
				break;
				case Ticket::TYPE_ORDER:
					return new Order($fields);
				break;
				default:
					return null;
				break;
			}
		}
	}

The hash data might look like:

	$hash = array(
		'ticket_id' => 123,
		'ticket_type_id' => Ticket::TYPE_QUOTE,
		'customer_id' => 312,
		'order_placed_datetime' => '2007-01-01 00:00:00'
	);


	
	$order = new Order($fields); // initializes the object with pre-existing data. `isLoaded` will return true, as it is assumed the pre-existing data was pulled from the source.

	$order = new Order($id); // loads from database. Find out if load succeed via `$order->isLoaded()` <- TODO: Rename that function.


	$order->load(); // loads from the database using the current key id. This method is useful when trying to construct a multi-PK object
		// for example:
		$order = new Order($multiKeyHash);
		$order->load();

		$ticket = Ticket::construct($hash); // will switch through the hash to figure out with type of object to return, an Order or a Quote.


Accessing Join attributes (this is collection-related topic)
-------------------------

Need to standardize the way of accessing join fields.


Static Construction Implementation
----------------------------------

After given this some thought, why don't we try this:

	protected static $dbName = 'asdlfjk';

the problem is that the member definition can only appear once, otherwise functions in parent classes where it is defined will not see the change. We can not overcome this by adding a `public static defineDbCOnfig()` method because the same problem will occur: parent classes will call the wrong one.

This might be why Propel uses a "Peer" class which contains static methods?

But, maybe one appearance is enough? If we are using the generator,then it can generate all these static methods and attributes for us. The question is how valuable is being able to override dbName, tableName, and all definitions (for fields, objects, and collections)? One would think the information is not dynamic at run time so there is no value in being able to override it -- you'd just change the values that are there. But, maybe someone wants to reuse some logic and they do it by extending an existing class and customizing some logic?  Sounds like in that case you should be using a different design pattern, perhaps the Strategy Pattern.

What if we generate, in the starter classes, the static methods and member variables that are needed?  This allows Cough to call the methods, allows the end user to customize them, and the only drawback I can think of is that hand-writing a Cough class might be harder (because of the static methods, the member variables are easy and required already).

Perhaps we should take a look at how much work there is in the static methods, and see if we can't have some of the static methods in the core Cough class... (e.g. can you do self::$dbName in a parent class when there isn't one defined until a sub class?)

Even if we don't want to require static methods, we could also provide an option for it. The problem is that we are trying to allow you to have a factory method that returns an object of the right type, but core Cough needs to know what that method is otherwise it will be stuck constructing only one type of object and not use your factory method (e.g. CoughCollection code that creates your elements... Now, we currently have an option to pass in the element name (if wanting to override it, but maybe we could set a variable for the factory method, if any, or even provide code that can be evaled))


Loading / Setting objects.
--------------------------

Listen closely, because this might solve the confusion about what setCollectionName_Collection() does (set the reference to the collection or call set on the collection which will "set" the state of the collection to what you give it, i.e. perform any needed adds and removes).

Here we go.

What if the load methods for objets and collections support parameters? For example, if you have preloaded a related object you need a way to set it so that an extra lookup isn't done. We were considering:


	<?php
	$manuf = new Manufacturer(1);
	$product = new Product(1);
	$product->setManufacturer_Object($manuf);
	
	// And then if you call get it doesn't load because the object is already available.
	$product->getManufacturer();
	?>

But what about:

	<?php
	$manuf = new Manufacturer(1);
	$product = new Product(1);
	$product->loadManufacturer_Object($manuf);
	?>

What we are saying here is that the load method will check arguments and only perform the load if nothing was passed in. If something was passed in, it will still be setting the object, it just won't do a database lookup.

NOTE: object loading should setObject (i.e. we are abstracting away the object data structure this time around, so use setObject/getObject)

Example object load method:

	<?php
	public function loadManufacturer_Object($hashOrObject = null) {
		if (is_null($hashOrObject)) {
			// Do db lookup to get hash.
			$sql = Manufacturer::getLoadSqlWithoutWhere();
			$sql . = ' WHERE ' . $this->getDb()->generateWhere($this->getPk())
		}
		else if (is_array($hashOrObject)) {
			// We got the data
		}
		else if (is_object($hashOrObject)) {
			// We got the object, just set it:
			$this->setObject('manufacturer', $hashOrObject);
		}
	}
	?>

Static methods will need a static db object too... we should provide a getter for non static methods and static methods alike:

	self::getDb()->generateWhere($this->getPk())

The method might look like:

	public static function getDb() {
		if (is_null(self::$db)) {
			self::$db = DatabaseFactory::getDatabase(self::$dbName);
		}
		return self::$db;
	}

If we go that route we might need to require that all generated classes implement a CoughStaticInterface or something that says the following methods must be defined:

	public static function getDb();
	public static function constructByPk($pk);
	public static function constructByFields($hash);
	public static function construct(); // ?
	// and more...


