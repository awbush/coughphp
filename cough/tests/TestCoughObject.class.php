<?php

class TestCoughObject extends UnitTestCase
{
	protected $db = null; // the database object
	
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
		$this->setUpDatabaseFactory();
	}
	
	public function setUpDatabaseFactory()
	{
		require_once(APP_PATH . 'dal/as_database/load.inc.php');
		As_DatabaseFactory::addDatabaseConfig('test_simpletest', array(
			'host' => '127.0.0.1',
			'user' => 'cough_test',
			'pass' => 'cough_test'
		));
		$this->db = As_DatabaseFactory::getDatabase('test_simpletest');
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
		$this->assertEqual('hello', 'hello');
	}
}

?>
