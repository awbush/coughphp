
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
