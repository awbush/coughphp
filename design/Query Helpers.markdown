Potential Query Classes
-----------------------

class Query {
	protected $db = null;
	public function __construct($db) {
		$this->setDb($db);
	}
	public function setDb($db) {
		$this->db = $db;
	}
	public function __toString() {
		return $this->getString();
	}
	// Override this one in sub query classes
	public function getString() {
		return '';
	}
	public function run() {
		return $this->db->query($this->getString());
	}
	public function execute() {
		return $this->db->execute($this->getString());
	}
	// Factory methods
	public static function getSelect($db) {
		// We could check if the db type is MS SQL and return a different object that supports TOP instead of LIMIT, e.g.
		return new SelectQuery($db);
	}
}

class SelectQuery extends Query {
	public function getString() {
		// ...
	}
	// setters and getters for SELECT queries
}

Cough might use it like this:
-----------------------------

// Here's how Cough does inserts currently
$db = $this->getDb();
$db->selectDb($this->getDbName());
$result = $db->insert($this->getTableName(), $fields);

// Using the Query object it might be like this
$db = $this->getDb();
$db->selectDb($this->getDbName());
$query = Query::getInsert($db);
$result = $db->query($query->getString()); // or maybe just $result = $query->run();

A user of Cough might use it like:
----------------------------------

class Product {
	public static function getBaseQuery() {
		$query = Query::getSelect(CoughDatabaseFactory::getDatabase('crump'));
		$query->setSelect('*');
		$query->addFrom('product');
		$query->addFrom('INNER JOIN manufacturer USING (manufacturer_id)');
	}
}

class Product_Collection {
	public static function getAll() {
		static $collection = null;
		if (is_null($collection)) {
			$query = Product::getBaseQuery();
			$collection = new Product_Collection();
			$collection->loadBySql($query->getString());
		}
		return $collection;
	}
}
