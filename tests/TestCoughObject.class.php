<?
ModuleFactory::loadModule('matt_database');
ModuleFactory::loadModule('cough');
die('Needs to be written');

class TestCoughObject extends UnitTestCase {

	protected $db = null;
	protected $tableName = null;

	public function setUp() {
		//DatabaseFactory::addDatabase('test_simpletest', new Database('test_simpletest', 'localhost', 'nobody', ''));
		//$this->db = DatabaseFactory::getDatabase('test_simpletest');
		$this->db = new Database('test_simpletest', 'localhost', 'nobody', '');
		$this->tableName = 'test_database';
		$this->createTable();
	}

	public function tearDown() {
		$this->dropTable();
	}

	protected function getDatabase() {
		return $this->db;
	}

	protected function createTable() {
		$this->dropTable();
		$db = $this->getDatabase();
		$sql = '
			CREATE TABLE  `' . $this->tableName . '` (
			 `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			 `name` VARCHAR( 255 ) NULL ,
			 `last_modified` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ,
			 `is_retired` TINYINT( 1 ) NOT NULL DEFAULT  "0"
			) ENGINE = innodb;
		';
		$db->doQuery($sql);
		$sql = '
			INSERT INTO `' . $this->tableName . '`
			SET name = "Initial Name";
		';
		$db->doQuery($sql);
	}

	protected function dropTable() {
		$sql = 'DROP TABLE IF EXISTS `'. $this->tableName . '`';
		$db = $this->getDatabase();
		$db->doQuery($sql);
	}

	//////////////////////////////////////
	// Tests...
	//////////////////////////////////////

	public function testDoSelect() {
		$db = $this->getDatabase();

		// Test that we get back exactly this:
		$match = array(
			'id' => 1,
			'name' => 'Initial Name',
			'last_modified' => date('Y-m-d H:i:s'),
			'is_retired' => 0
		);
		$result = $db->doSelect($this->tableName);
		$this->assertTrue(($row = $result->getRow()), 'Row found');
		if ($row) {
			$this->assertEqual($row, $match);
		}

		// Test different selectors with same where condition

		$where = array('id' => 1);

		// Custom string selector
		$result = $db->doSelect($this->tableName, 'id as foo_id', $where);
		$this->assertTrue(($row = $result->getRow()), 'Row found');
		if ($row) {
			$this->assertNotEqual($row, $match);
			$this->assertEqual($row, array('foo_id' => 1));
		}

		// Custom array selector
		$result = $db->doSelect($this->tableName, array('id', 'name'), $where);
		$this->assertTrue(($row = $result->getRow()), 'Row found');
		if ($row) {
			$this->assertNotEqual($row, $match);
			$this->assertEqual($row, array('id' => 1, 'name' => 'Initial Name'));
		}

		$this->assertNoErrors();
	}

	public function testDoInsertAutoQuote() {
		$db = $this->getDatabase();
		$fields = array(
			'name' => 'Test Name'
		);
		$id = $db->insert($this->tableName, $fields);
		$this->assertTrue(($id > 0), 'insert id is greater than 0.');
		$this->assertNoErrors();
	}
}

?>
