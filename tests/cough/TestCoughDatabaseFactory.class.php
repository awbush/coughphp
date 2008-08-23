<?php

class TestCoughDatabaseFactory extends UnitTestCase
{
	protected $configs = array();
	
	//////////////////////////////////////
	// Set Up
	//////////////////////////////////////
	
	public function __construct()
	{
		$coughRoot = dirname(dirname(dirname(__FILE__)));
		require_once($coughRoot . '/cough/load.inc.php');
		require_once($coughRoot . '/as_database/load.inc.php');
	}
	
	public function setUp()
	{
		CoughDatabaseFactory::reset();
		$this->configs = array(
			'config1' => array(
				'host' => 'localhost',
				'user' => 'nobody',
				'port'	=> '3306',
				'aliases' => array('testConfigAlias', 'reporting_server' => 'reports')
			),
			'config2' => array(
				'host' => 'localhost',
				'user' => 'nobody',
				'port'	=> '3307',
				'db_name_hash' => array('db_alias' => 'actual_db_name', 'write_server' => 'reports')
			)
		);
	}
	
	//////////////////////////////////////
	// Tests...
	//////////////////////////////////////
	
	public function assertGetDatabaseNameWorks()
	{
		// An alias specified without the hash should have alias *and* database name equal to each other.
		$this->assertIdentical(CoughDatabaseFactory::getDatabaseName('testConfigAlias'), 'testConfigAlias');
		
		// Specifying an alias with a hash allows access to the value stored therein.
		$this->assertIdentical(CoughDatabaseFactory::getDatabaseName('reporting_server'), 'reports');
		
		// Trying to access a database name that doesn't have a mapping should return the original value.
		$this->assertIdentical(CoughDatabaseFactory::getDatabaseName('reports'), 'reports');
		
		// Specifying the db_name_hash allows a more direct way of configuring the aliases and database name mappings
		$this->assertIdentical(CoughDatabaseFactory::getDatabaseName('db_alias'), 'actual_db_name');
		$this->assertIdentical(CoughDatabaseFactory::getDatabaseName('write_server'), 'reports');
	}
	
	public function testAddConfig()
	{
		// Configs can be added by specified the "aliases" parameter with a mix of old (values only) and new (hash) styles
		CoughDatabaseFactory::addConfig($this->configs['config1']);
		
		// Configs can be specified with the new "db_name_hash" parameter to increase effeciency
		CoughDatabaseFactory::addConfig($this->configs['config2']);
		
		$this->assertGetDatabaseNameWorks();
	}
	
	public function testSetConfigs()
	{
		CoughDatabaseFactory::setConfigs(array(
			$this->configs['config1'],
			$this->configs['config2'],
		));
		
		$this->assertGetDatabaseNameWorks();
	}
	
	public function testGetDatabase()
	{
		// 2008-08-18/AWB: As of CoughPHP 1.3, an exception will be thrown with message
		// saying to check your config.  It should mention something about
		// CoughDatabaseFactory::addConfig(), and possibly include a URL for more
		// documentation.
		
		// // trying to access a non-existing database object should return null
		// $this->assertNull(CoughDatabaseFactory::getDatabase('missingDbAlias'));
		
		// trying to access a non-existing database object should throw an exception
		try
		{
			CoughDatabaseFactory::getDatabase('missingDbAlias');
			$threwException = false;
		}
		catch (Exception $e)
		{
			$threwException = true;
		}
		$this->assertTrue($threwException, 'Should throw an exception when DB alias does not exist.');
		
		// test default adapter = "as" and old style "aliases" param
		$testDbConfig = array(
			'driver' => 'mysql',
			'host' => 'localhost',
			'user' => 'cough_test',
			'pass' => 'cough_test',
			'port' => '3306',
			'aliases' => array('cough_test1')
		);
		CoughDatabaseFactory::addConfig($testDbConfig);
		$test1Db = CoughDatabaseFactory::getDatabase('cough_test1');
		$this->assertIsA($test1Db, 'CoughAsDatabase');
		
		// test specifying adapter as "as" and new "db_name_hash" param
		$testDbConfig = array(
			'adapter' => 'as',
			'driver' => 'mysql',
			'host' => 'localhost',
			'user' => 'cough_test',
			'pass' => 'cough_test',
			'port' => '3306',
			'db_name_hash' => array('cough_test2' => 'test_cough_object')
		);
		CoughDatabaseFactory::addConfig($testDbConfig);
		$test2Db = CoughDatabaseFactory::getDatabase('cough_test2');
		$this->assertIsA($test2Db, 'CoughAsDatabase');
		
		// test new style aliases param
		$testDbConfig = array(
			'driver' => 'mysql',
			'host' => 'localhost',
			'user' => 'cough_test',
			'pass' => 'cough_test',
			'port' => '3306',
			'aliases' => array('cough_test4' => 'test_cough_object')
		);
		CoughDatabaseFactory::addConfig($testDbConfig);
		$test4Db = CoughDatabaseFactory::getDatabase('cough_test4');
		$this->assertIsA($test4Db, 'CoughAsDatabase');
		
		// // test specifying adapter as "pdo"
		// $testDbConfig = array(
		// 	'adapter' => 'pdo',
		// 	'driver' => 'mysql',
		// 	'host' => 'localhost',
		// 	'user' => 'cough_test',
		// 	'pass' => 'cough_test',
		// 	'port' => '3306',
		// 	'aliases' => array('cough_test3' => 'test_cough_object')
		// );
		// CoughDatabaseFactory::addConfig($testDbConfig);
		// $test3Db = CoughDatabaseFactory::getDatabase('cough_test3');
		// $this->assertIsA($test3Db, 'CoughPdoDatabaseAdapter');
		
		// test getting the same database object back
		$test1DbReference = CoughDatabaseFactory::getDatabase('cough_test1');
		$this->assertReference($test1Db, $test1DbReference);
		$this->assertIdentical($test1Db, $test1DbReference);
		$this->assertFalse($test2Db === $test1DbReference);
		// $this->assertFalse($test3Db === $test1DbReference);
	}
}

?>
