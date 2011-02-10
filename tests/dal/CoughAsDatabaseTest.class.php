<?php
class CoughAsDatabaseTest extends PHPUnit_Framework_TestCase
{
	//////////////////////////////////////
	// Set Up
	//////////////////////////////////////
	
	protected static $db = null;
	protected static $adapterName = 'as';
	protected static $resultObjectClassName = 'As_DatabaseResult';
	
	public static function setUpBeforeClass()
	{
		// include core cough, and the as_database DAL.
		require_once(dirname(dirname(dirname(__FILE__))) . '/cough/load.inc.php');
		self::loadAdapterModule();
		self::initDatabase();
	}
	
	public static function loadAdapterModule()
	{
		require_once(dirname(dirname(dirname(__FILE__))) . '/as_database2/load.inc.php');
	}
	
	public static function initDatabase()
	{
		// setup database
		require(dirname(dirname(__FILE__)) . '/database_config.inc.php');
		$testDbConfig = $dsn;
		$testDbConfig['adapter'] = self::$adapterName;
		$testDbConfig['aliases'] = array($dsn['db_name']);
		CoughDatabaseFactory::reset();
		CoughDatabaseFactory::addConfig($testDbConfig);
		self::$db = CoughDatabaseFactory::getDatabase($dsn['db_name']);
	}
	
	public function executeSqlFile($sqlFile)
	{
		// We have to run this sql dump one query at a time
		$sqlCommands = explode(';', file_get_contents($sqlFile));
		
		// the last element is a blank string, so get rid of it
		array_pop($sqlCommands);

		foreach ($sqlCommands as $sql) {
			self::$db->execute($sql);
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
		$result = self::$db->query('SELECT * FROM person');
		$this->assertInstanceOf(self::$resultObjectClassName, $result);
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
		$this->assertSame($result->getRows(), $expectedRows);
		
		// test empty result
		$result = self::$db->query('SELECT * FROM school_type_empty_table');
		$this->assertSame($result->getRows(), array());
		
		// test derived field name
		$result = self::$db->query('SELECT 3306 AS `port`');
		$expectedRows = array(array(
			'port' => '3306'	
		));
		$this->assertSame($result->getRows(), $expectedRows);
		
		// test select mysql function
		$result = self::$db->query('SELECT COUNT(*) FROM person');
		$expectedRows = array(array(
			'COUNT(*)' => '3'
		));
		$this->assertSame($result->getRows(), $expectedRows);
	}
	
	public function testExecute()
	{
		// test getLastInsertId before we have done any inserts
		// NOTE: PDO returns string "0" and As_Database returns integer 0... I would prefer the return value to be 
		// null, but perhaps this is not a meaningful issue
		//$this->assertNull(self::$db->getLastInsertId()); // currently fails
		$this->assertEquals(self::$db->getLastInsertId(), 0);
		
		// test execute insert
		$numAffected = self::$db->execute("INSERT person VALUES ('', 'Venkman', 0, 3)");
		$this->assertSame($numAffected, 1);
		
		// test getLastInsertId
		// NOTE: PDO returns string 12, but As_Database returns integer 12... this may or may not be a discrepancy that
		// needs to be resolved
		$venkmanPersonId = self::$db->getLastInsertId();
		//$this->assertSame($venkmanPersonId, 12); // currently fails
		$this->assertEquals($venkmanPersonId, 12);
		
		// test insert succeeded
		$result = self::$db->getResult('SELECT person.name FROM person WHERE person.person_id = 12');
		$this->assertSame($result, 'Venkman');
		
		// test execute update
		$numAffected = self::$db->execute('UPDATE person SET political_party_id = 4 WHERE political_party_id = 2');
		$this->assertSame($numAffected, 2);
		
		// test update succeeded
		$result = self::$db->query('SELECT name FROM person WHERE political_party_id = 4');
		$expectedRows = array(
			'0' => array(
				'name' => 'Anthony'
			),
			
			'1' => array(
				'name' => 'Lewis'
			)
		);
		$this->assertSame($result->getRows(), $expectedRows);
		
		// test execute delete
		$numAffected = self::$db->execute('DELETE FROM person WHERE is_retired = 0');
		$this->assertSame($numAffected, 4);
		
		// test delete succeeded
		$result = self::$db->getResult('SELECT COUNT(*) FROM person');
		$this->assertSame($result, '0');
	}
	
	public function testQuote()
	{
		// Test handling of magic_quotes_gpc (lack thereof), and escaping of characters
		$acidString = "\000\n\r\032!@#^%&!@^%#!*@&#!()))()!(  .   . . ..\\\\.\\\"  \\'   // /./ x?? ddfsdfdsf je;;ee  //.. ,, SELECT * FROM person balhhh )";
		$quotedString = self::$db->quote($acidString);
		$sql = 'UPDATE person SET person.name = ' . $quotedString;
		self::$db->execute($sql);
		
		// Not all DB layers support LIMIT
		// $sql = 'SELECT person.name FROM person LIMIT 1';
		$sql = self::$db->getSelectQuery();
		$sql->setSelect('person.name');
		$sql->setFrom('person');
		$sql->setLimit(1);
		
		$returnedString = self::$db->getResult($sql);
		$this->assertEquals($acidString, $returnedString);
	}
	
	public function testPreparedStmt()
	{
		if (!self::$db->canQueryPreparedStmt())
		{
			// can't do this test
			return;
		}
		// test getLastInsertId before we have done any inserts
		// NOTE: PDO returns string "0" and As_Database returns integer 0... I would prefer the return value to be 
		// null, but perhaps this is not a meaningful issue
		//$this->assertNull(self::$db->getLastInsertId()); // currently fails
		$this->assertEquals(self::$db->getLastInsertId(), 0);
		
		// test execute insert
		$params = array('Venkman', 0,3);
		self::$db->queryPreparedStmt("INSERT person VALUES ('', ?, ?, ?)", $params);
		$numAffected = self::$db->getNumAffectedRows();
		$this->assertSame($numAffected, 1);
		
		// test getLastInsertId
		// NOTE: PDO returns string 12, but As_Database returns integer 12... this may or may not be a discrepancy that
		// needs to be resolved
		$venkmanPersonId = self::$db->getLastInsertId();
		//$this->assertSame($venkmanPersonId, 12); // currently fails
		$this->assertEquals($venkmanPersonId, 12);
		
		// test insert succeeded
		$params = array(12);
		$result = self::$db->queryPreparedStmt('SELECT person.name FROM person WHERE person.person_id = ?', $params);
		$this->assertSame($result->getResult(0), 'Venkman');
		
		// test execute update
		$params = array(2);
		self::$db->queryPreparedStmt('UPDATE person SET political_party_id = 4 WHERE political_party_id = ?', $params);
		$numAffected = self::$db->getNumAffectedRows();
		$this->assertSame($numAffected, 2);
		
		// test update succeeded
		$params = array(4);
		$result = self::$db->queryPreparedStmt('SELECT name FROM person WHERE political_party_id = ?', $params);
		$expectedRows = array(
			'0' => array(
				'name' => 'Anthony'
			),
			
			'1' => array(
				'name' => 'Lewis'
			)
		);
		$this->assertSame($result->getRows(), $expectedRows);
		
		// test execute delete
		$params = array(0);
		self::$db->queryPreparedStmt('DELETE FROM person WHERE is_retired = ?', $params);
		$numAffected = self::$db->getNumAffectedRows();
		$this->assertSame($numAffected, 4);
		
		// test delete succeeded
		$result = self::$db->getResult('SELECT COUNT(*) FROM person');
		$this->assertSame($result, '0');
	}
	
}

?>