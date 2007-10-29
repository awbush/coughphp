<?php

class TestCoughDatabaseFactory extends UnitTestCase
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
		require_once(APP_PATH . 'dalal/CoughDatabaseFactory.class.php');
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
		// TODO close the db connection?
	}

	//////////////////////////////////////
	// Tests...
	//////////////////////////////////////
	
	public function testAddDatabaseConfig()
	{
		$testConfig = array(
			'db_name' => 'testDbName',
			'port'	=> '3307'
		);
		
		$expectedConfig = $testConfig;
		
		CoughDatabaseFactory::addDatabaseConfig('testConfigAlias', $testConfig);
		$dbConfigs = CoughDatabaseFactory::getDatabaseConfigs();
		
		$this->assertNotNull($dbConfigs['testConfigAlias']);
		$this->assertIdentical($dbConfigs['testConfigAlias'], $expectedConfig);
	}
	
	public function testSetDatabaseConfigs()
	{
		$testConfigs = array(		
			'testConfig1' => array(
				'db_name' => 'testConfigDbName',
				'port'	=> '3307'
			),
		
			'testConfig2' => array(
				'driver' => 'mysql',
				'host' => 'localhost',
				'db_name' => 'test2DbName',
				'user' => 'user2',
				'pass' => 'pass2'
			),
		
			'testConfig3' => array(
				'driver' => 'postgres',
				'host' => 'testconfig2.domain.tld',
				'db_name' => 'testDbName',
				'user' => null,
				'pass' => null,
				'port' => '3306'
			)
		);
		
		$expectedConfigs = $testConfigs;
		
		CoughDatabaseFactory::setDatabaseConfigs($testConfigs);
		$dbConfigs = CoughDatabaseFactory::getDatabaseConfigs();
		$this->assertIdentical($dbConfigs, $expectedConfigs);
	}
	
	public function testAddDatabase()
	{
		$this->assertIdentical(CoughDatabaseFactory::getDatabases(), array());
		$testDbObject = array('db' => 'pretend this is a db object');
		CoughDatabaseFactory::addDatabase('testDbAlias', $testDbObject);
		$databases = CoughDatabaseFactory::getDatabases();
		$this->assertNotNull($databases['testDbAlias']);
		$this->assertIdentical($databases['testDbAlias'], $testDbObject);
	}
	
	public function testGetDatabase()
	{
		// test missing database config behavior
		$this->assertNull(CoughDatabaseFactory::getDatabase('missingDbAlias'));
		
		// test existing database behavior
		$testDbObject = array('db' => 'pretend this is a db object');
		CoughDatabaseFactory::addDatabase('existingDbAlias', $testDbObject);
		$this->assertIdentical(CoughDatabaseFactory::getDatabase('existingDbAlias'), $testDbObject);
		
		// test default adapter = "as"
		$testDbConfig = array(
			'driver' => 'mysql',
			'host' => 'localhost',
			'db_name' => 'test_cough_object',
			'user' => 'cough_test',
			'pass' => 'cough_test',
			'port' => '3306'
		);
		CoughDatabaseFactory::addDatabaseConfig('cough_test1', $testDbConfig);
		$test1Db = CoughDatabaseFactory::getDatabase('cough_test1');
		$this->assertIsA($test1Db, 'CoughAsDatabaseAdapter');
		
		// test specifying adapter as "as"
		$testDbConfig = array(
			'adapter' => 'as',
			'driver' => 'mysql',
			'host' => 'localhost',
			'db_name' => 'test_cough_object',
			'user' => 'cough_test',
			'pass' => 'cough_test',
			'port' => '3306'
		);
		CoughDatabaseFactory::addDatabaseConfig('cough_test2', $testDbConfig);
		$test2Db = CoughDatabaseFactory::getDatabase('cough_test2');
		$this->assertIsA($test2Db, 'CoughAsDatabaseAdapter');
		
		// test specifying adapter as "pdo"
		$testDbConfig = array(
			'adapter' => 'pdo',
			'driver' => 'mysql',
			'host' => 'localhost',
			'db_name' => 'test_cough_object',
			'user' => 'cough_test',
			'pass' => 'cough_test',
			'port' => '3306'
		);
		CoughDatabaseFactory::addDatabaseConfig('cough_test3', $testDbConfig);
		$test3Db = CoughDatabaseFactory::getDatabase('cough_test3');
		$this->assertIsA($test3Db, 'CoughPdoDatabaseAdapter');
		
		// test getting the same database object back
		$test1DbReference = CoughDatabaseFactory::getDatabase('cough_test1');
		$this->assertReference($test1Db, $test1DbReference);
		$this->assertIdentical($test1Db, $test1DbReference);
		$this->assertNotIdentical($test2Db, $test1DbReference);
		$this->assertNotIdentical($test3Db, $test1DbReference);
	}
}

?>