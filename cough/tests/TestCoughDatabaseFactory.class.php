<?php

class TestCoughDatabaseFactory extends UnitTestCase
{
	//////////////////////////////////////
	// Set Up
	//////////////////////////////////////
	
	public function __construct()
	{
		require_once(dirname(dirname(dirname(__FILE__))) . '/load.inc.php');
	}
	
	//////////////////////////////////////
	// Tests...
	//////////////////////////////////////
	
	public function testAddConfig()
	{
		// 2008-06-11/AWB: I don't really like this test; it seems coupled to the internals of CoughDatabaseFactory...
		
		$testConfig = array(
			'db_name' => 'testDbName',
			'port'	=> '3307',
			'aliases' => array('testConfigAlias')
		);
		
		$expectedConfig = $testConfig;
		
		CoughDatabaseFactory::addConfig($testConfig);
		$dbConfigs = CoughDatabaseFactory::getConfigs();
		
		$this->assertIdentical(count($dbConfigs), 1);
		$this->assertIdentical($dbConfigs[0], $testConfig);
	}
	
	public function testSetConfigs()
	{
		return;
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
		
		CoughDatabaseFactory::setConfigs($testConfigs);
		$dbConfigs = CoughDatabaseFactory::getConfigs();
		$this->assertIdentical($dbConfigs, $expectedConfigs);
	}
	
	public function testAddDatabase()
	{
		return;
		$this->assertIdentical(CoughDatabaseFactory::getDatabases(), array());
		$testDbObject = array('db' => 'pretend this is a db object');
		CoughDatabaseFactory::addDatabase('testDbAlias', $testDbObject);
		$databases = CoughDatabaseFactory::getDatabases();
		$this->assertNotNull($databases['testDbAlias']);
		$this->assertIdentical($databases['testDbAlias'], $testDbObject);
	}
	
	public function testGetDatabase()
	{
		return;
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
		CoughDatabaseFactory::addConfig('cough_test1', $testDbConfig);
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
		CoughDatabaseFactory::addConfig('cough_test2', $testDbConfig);
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
		CoughDatabaseFactory::addConfig('cough_test3', $testDbConfig);
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
