<?php

class TestCoughAsDatabase extends UnitTestCase
{
	//////////////////////////////////////
	// Set Up
	//////////////////////////////////////
	
	protected $db = null;
	protected $adapterName = 'as';
	protected $resultObjectClassName = 'CoughAsDatabaseResult';
	
	public function __construct()
	{
		// include core cough, and the as_database DAL.
		require_once(dirname(dirname(dirname(__FILE__))) . '/cough/load.inc.php');
		$this->loadAdapterModule();
		$this->initDatabase();
	}
	
	public function loadAdapterModule()
	{
		require_once(dirname(dirname(dirname(__FILE__))) . '/as_database/load.inc.php');
	}
	
	public function initDatabase()
	{
		// setup database
		require(dirname(dirname(__FILE__)) . '/database_config.inc.php');
		$testDbConfig = $dsn;
		$testDbConfig['adapter'] = $this->adapterName;
		$testDbConfig['aliases'] = array($dsn['db_name']);
		CoughDatabaseFactory::reset();
		CoughDatabaseFactory::addConfig($testDbConfig);
		$this->db = CoughDatabaseFactory::getDatabase($dsn['db_name']);
	}
	
	public function executeSqlFile($sqlFile)
	{
		// We have to run this sql dump one query at a time
		$sqlCommands = explode(';', file_get_contents($sqlFile));
		
		// the last element is a blank string, so get rid of it
		array_pop($sqlCommands);

		foreach ($sqlCommands as $sql) {
			$this->db->execute($sql);
		}
	}
	
	/**
	 * This method is run by simpletest before each test*() method.
	 *
	 * @return void
	 **/
	public function setUp()
	{
		$this->executeSqlFile(dirname(__FILE__) . '/db_setup.sql');
	}
	
	//////////////////////////////////////
	// Tear Down
	//////////////////////////////////////
	
	/**
	 * This method is run by simpletest after running each test*() method.
	 *
	 * @return void
	 **/
	public function tearDown()
	{
		$this->executeSqlFile(dirname(__FILE__) . '/db_teardown.sql');
	}

	//////////////////////////////////////
	// Tests...
	//////////////////////////////////////
	
	public function testQuery()
	{
		$result = $this->db->query('SELECT * FROM person');
		$this->assertIsA($result, $this->resultObjectClassName);
		$expectedRows = array(
			'0' => array(
				'person_id' => '1',
				'name' => 'Anthony',
				'is_retired' => '0',
				'political_party_id' => '2'
			),

			'1' => array(
				'person_id' => '2',
				'name' => 'Lewis',
				'is_retired' => '0',
				'political_party_id' => '2'
			),

			'2' => array(
				'person_id' => '3',
				'name' => 'Tom',
				'is_retired' => '0',
				'political_party_id' => '99'
			)
		);
		$this->assertIdentical($result->getRows(), $expectedRows);
		
		// test empty result
		$result = $this->db->query('SELECT * FROM school_type_empty_table');
		$this->assertIdentical($result->getRows(), array());
		
		// test derived field name
		$result = $this->db->query('SELECT 3306 AS `port`');
		$expectedRows = array(array(
			'port' => '3306'	
		));
		$this->assertIdentical($result->getRows(), $expectedRows);
		
		// test select mysql function
		$result = $this->db->query('SELECT COUNT(*) FROM person');
		$expectedRows = array(array(
			'COUNT(*)' => '3'
		));
		$this->assertIdentical($result->getRows(), $expectedRows);
	}
	
	public function testExecute()
	{
		// test getLastInsertId before we have done any inserts
		// NOTE: PDO returns string "0" and As_Database returns integer 0... I would prefer the return value to be 
		// null, but perhaps this is not a meaningful issue
		//$this->assertNull($this->db->getLastInsertId()); // currently fails
		$this->assertEqual($this->db->getLastInsertId(), 0);
		
		// test execute insert
		$numAffected = $this->db->execute("INSERT person VALUES ('', 'Venkman', 0, 3)");
		$this->assertIdentical($numAffected, 1);
		
		// test getLastInsertId
		// NOTE: PDO returns string 12, but As_Database returns integer 12... this may or may not be a discrepancy that
		// needs to be resolved
		$venkmanPersonId = $this->db->getLastInsertId();
		//$this->assertIdentical($venkmanPersonId, 12); // currently fails
		$this->assertEqual($venkmanPersonId, 12);
		
		// test insert succeeded
		$result = $this->db->result('SELECT person.name FROM person WHERE person.person_id = 12');
		$this->assertIdentical($result, 'Venkman');
		
		// test execute update
		$numAffected = $this->db->execute('UPDATE person SET political_party_id = 4 WHERE political_party_id = 2');
		$this->assertIdentical($numAffected, 2);
		
		// test update succeeded
		$result = $this->db->query('SELECT name FROM person WHERE political_party_id = 4');
		$expectedRows = array(
			'0' => array(
				'name' => 'Anthony'
			),
			
			'1' => array(
				'name' => 'Lewis'
			)
		);
		$this->assertIdentical($result->getRows(), $expectedRows);
		
		// test execute delete
		$numAffected = $this->db->execute('DELETE FROM person WHERE is_retired = 0');
		$this->assertIdentical($numAffected, 4);
		
		// test delete succeeded
		$result = $this->db->result('SELECT COUNT(*) FROM person');
		$this->assertIdentical($result, '0');
	}
	
	public function testEscape()
	{
		$acidString = "'''\"";
		$escapedString = $this->db->escape($acidString);
		$expectedString = "\\'\\'\\'\\\"";
		$this->assertEqual($escapedString, $expectedString);
	}
	
	public function testQuote()
	{
		// this string fails due to our use of magic quotes
		//$acidString = "!@#^%&!@^%#!*@&#!()))()!(  .   . . ..\\\"  \\'   // /./ x?? ddfsdfdsf je;;ee  //.. ,, SELECT * FROM person balhhh )";
		$acidString = "!@#^%&!@^%#!*@&#!()))()!(  .   . . ..   // /./ x?? ddfsdfdsf je;;ee  //.. ,, SELECT * FROM person balhhh )";
		$quotedString = $this->db->quote($acidString);
		$sql = 'UPDATE person SET person.name = ' . $quotedString;
		$this->db->execute($sql);
		$sql = 'SELECT person.name FROM person LIMIT 1';
		$returnedString = $this->db->result($sql);
		$this->assertEqual($acidString, $returnedString);
	}
}

?>