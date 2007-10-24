<?php

class TestCoughObject extends UnitTestCase
{
	protected $db = null; // the database object
	protected $coughTestDbResetSql = '';
	
	//////////////////////////////////////
	// Set Up
	//////////////////////////////////////
	
	/**
	 * This method is run by simpletest before running all test*() methods.
	 *
	 * @return void
	 **/
	public function setUp()
	{
		$this->includeDependencies();
		
		$testDbConfig = array(
			'driver' => 'mysql',
			'host' => '127.0.0.1', // TODO: localhost does not work for me???
			'db_name' => 'test_cough_object',
			'user' => 'cough_test',
			'pass' => 'cough_test',
			'port' => '3306'
		);
		
		CoughDatabaseFactory::addDatabaseConfig('test_cough_object', $testDbConfig);
		$this->db = CoughDatabaseFactory::getDatabase('test_cough_object');
		
		// We have to run this sql dump one query at a time
		$this->coughTestDbResetSql = explode(';', file_get_contents(dirname(__FILE__) . '/test_cough_object.sql'));
		
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
	
	public function includeDependencies()
	{
		require_once(dirname(dirname(dirname(__FILE__))) . '/load.inc.php');
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
	}

	//////////////////////////////////////
	// Tests...
	//////////////////////////////////////
	
	public function testEcho()
	{
		
	}
}

?>
