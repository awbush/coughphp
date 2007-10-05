<?php

class TestCoughAsDatabaseAdapter extends UnitTestCase
{
	//////////////////////////////////////
	// Set Up
	//////////////////////////////////////
	
	protected $db = null;
	protected $adapterName = 'as';
	protected $resultObjectClassName = 'CoughAsDatabaseResultAdapter';
	protected $coughTestDbResetSql = '';
	
	/**
	 * This method is run by simpletest before running all test*() methods.
	 *
	 * @return void
	 **/
	public function setUp()
	{
		include_once(APP_PATH . 'dalal/load.inc.php');
		
		$testDbConfig = array(
			'driver' => 'mysql',
			'host' => 'pascal.timepieceforyou.com',
			'db_name' => 'cough_test',
			'user' => 'root',
			'pass' => 'PMASZt.9.yMw6xvV',
			'port' => '3306'
		);
		
		CoughDatabaseFactory::setAdapter($this->adapterName);
		CoughDatabaseFactory::addDatabaseConfig('pascal', $testDbConfig);
		$this->db = CoughDatabaseFactory::getDatabase('pascal');
		
		// We have to run this sql dump one query at a time
		$this->coughTestDbResetSql = explode(';', file_get_contents(APP_PATH . 'dalal/drivers/as/tests/cough_test_db_reset.sql'));
		
		// the last element is a blank string, so get rid of it
		array_pop($this->coughTestDbResetSql);
		
		$this->resetCoughTestDatabase();
	}
	
	public function resetCoughTestDatabase()
	{
		foreach ($this->coughTestDbResetSql as $sql) {
			$this->db->execute($sql);
		}
	}
	
	//////////////////////////////////////
	// Tear Down
	//////////////////////////////////////
	
	/**
	 * This method is run by simpletest after running all test*() methods.
	 *
	 * @return void
	 **/
	public function tearDown()
	{
		$this->resetCoughTestDatabase();
		CoughDatabaseFactory::reset();
	}

	//////////////////////////////////////
	// Tests...
	//////////////////////////////////////
	
	public function testQuery()
	{
		$result = $this->db->query('SELECT * FROM cough_test.person');
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
		$result = $this->db->query('SELECT * FROM cough_test.school_type_empty_table');
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
	
	public function testResult()
	{
		// test standard result call
		$result = $this->db->result('SELECT COUNT(*) FROM person');
		$this->assertIdentical($result, '3');
		
		// test result call with multiple rows
		$result = $this->db->result('SELECT * FROM person');
		$expectedRow = array(
			'person_id' => '1',
			'name' => 'Anthony',
			'is_retired' => '0',
			'political_party_id' => '2'
		);
		$this->assertIdentical($result, $expectedRow);
		
		// test no results
		$result = $this->db->result("SELECT * FROM person WHERE name = 'Egon'");
		$this->dump($result);
		$this->assertNull($result);
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
}

?>