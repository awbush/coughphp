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
		$this->setUpDatabase();
		$this->initializeDatabase();
		$this->resetCoughTestDatabase();
		$this->includeCoughTestClasses();
	}
	
	public function resetCoughTestDatabase()
	{
		foreach ($this->coughTestDbResetSql as $sql) {
			$this->db->execute($sql);
		}
	}
	
	public function setUpDatabase()
	{
		$testDbConfig = array(
			'driver' => 'mysql',
			'host' => 'localhost', // TODO: localhost does not work for me???
			'db_name' => 'test_cough_object',
			'user' => 'cough_test',
			'pass' => 'cough_test',
			'port' => '3306'
		);
		
		CoughDatabaseFactory::addDatabaseConfig('test_cough_object', $testDbConfig);
		$this->db = CoughDatabaseFactory::getDatabase('test_cough_object');
	}
	
	public function initializeDatabase()
	{
		// We have to run this sql dump one query at a time
		$this->coughTestDbResetSql = explode(';', file_get_contents(dirname(__FILE__) . '/test_cough_object.sql'));
		
		// the last element is a blank string, so get rid of it
		array_pop($this->coughTestDbResetSql);
	}
	
	public function includeDependencies()
	{
		// include Cough + dependencies; this should be the only include necessary
		require_once(dirname(dirname(dirname(__FILE__))) . '/load.inc.php');
	}
	
	public function includeCoughTestClasses()
	{
		$classPath = dirname(dirname(dirname(__FILE__))) . '/config/test_cough_object/output/';
		// include Cough generated classes
		foreach (glob($classPath . 'generated/*.php') as $filename) {
			require_once($filename);
		}
		
		// include Cough user classes
		foreach (glob($classPath . 'concrete/*.php') as $filename) {
			require_once($filename);
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
	}

	//////////////////////////////////////
	// Tests...
	//////////////////////////////////////
	
	public function testCreateObject()
	{
		$newBook = new Book();
		$newBook->setTitle('Ulysses');
		$newBook->setIntroduction('1264 pages of bs by one of the masters.');
		$newBook->setCreationDatetime(date('Y-m-d H:i:s'));
		$newBook->save();
		
		$this->assertIdentical($newBook->getBookId(), 1);
		
		$this->resetCoughTestDatabase();
	}
	
	public function testLoadObject()
	{
		$newBook = new Book();
		$newBook->setTitle('Ulysses');
		$newBook->setIntroduction('1264 pages of bs by one of the masters.');
		$newBook->setCreationDatetime(date('Y-m-d H:i:s'));
		$newBook->save();
		
		$sameBook = Book::constructByKey(1);
		$this->assertIdentical($newBook->getBookId(), $sameBook->getBookId());
		$this->assertIdentical($newBook->getTitle(), $sameBook->getTitle());
		$this->assertIdentical($newBook->getIntroduction(), $sameBook->getIntroduction());
		$this->assertIdentical($newBook->getCreationDatetime(), $sameBook->getCreationDatetime());
		
		$this->resetCoughTestDatabase();
	}
	
	public function testChangeObject()
	{
		
	}
	
	public function testRetireObject()
	{
		
	}
	
	public function testDeleteObject()
	{
		
	}
}

?>
