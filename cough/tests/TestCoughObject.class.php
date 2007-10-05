<?php

class TestCoughObject extends UnitTestCase
{
	
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
		include_once(APP_PATH . 'load.inc.php');
		$this->setUpDatabaseFactory();
		$this->setUpCough();
	}
	
	public function setUpDatabaseFactory()
	{
		include_once(APP_PATH . 'dal/as_database/load.inc.php');
		As_DatabaseFactory::addDatabaseConfig('test_simpletest', array(
			'host' => 'localhost',
			'user' => 'nobody',
			'pass' => ''
		));
	}
	
	public function setUpCough()
	{
		// include_once(APP_PATH . 'cough/load.inc.php');
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
		$str = 'Test Echo';
		ob_start();
		echo $str;
		$contents = ob_get_clean();
		
		$this->assertEqual($contents, $str);
	}
	
	public function testFailure()
	{
		$this->assertEqual(1, 1);
	}
	
}

?>
