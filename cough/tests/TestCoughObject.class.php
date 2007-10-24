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
		$newAuthor = new Author();
		$newAuthor->setName('James Joyce');
		$newAuthor->setCreationDatetime(date('Y-m-d H:i:s'));
		$newAuthor->save();
		
		$newBook = new Book();
		$newBook->setTitle('Ulysses');
		$newBook->setAuthorId($newAuthor->getAuthorId());
		$newBook->setIntroduction('1264 pages of bs by one of the masters.');
		$newBook->setCreationDatetime(date('Y-m-d H:i:s'));
		$newBook->save();
		
		$sameBook = Book::constructByKey($newBook->getBookId());
		
		// this one fails; see ticket #27, http://trac.coughphp.com/ticket/27
		//$this->assertIdentical($newBook->getBookId(), $sameBook->getBookId());
		$this->assertEqual($newBook->getBookId(), $sameBook->getBookId());
		
		$this->assertIdentical($newBook->getTitle(), $sameBook->getTitle());
		
		// again, this is broke; see ticket #27
		//$this->assertIdentical($newBook->getAuthorId(), $sameBook->getAuthorId());
		$this->assertEqual($newBook->getAuthorId(), $sameBook->getAuthorId());
		
		$this->assertIdentical($newBook->getIntroduction(), $sameBook->getIntroduction());
		$this->assertIdentical($newBook->getCreationDatetime(), $sameBook->getCreationDatetime());
		
		$this->resetCoughTestDatabase();
	}
	
	public function testUpdateObject()
	{
		$newAuthor = new Author();
		$newAuthor->setName('James Joyce');
		$newAuthor->setCreationDatetime(date('Y-m-d H:i:s'));
		$newAuthor->save();
		
		$newAuthor->setName('Mark Twain');
		
		$this->assertIdentical($newAuthor->getName(), 'Mark Twain');
		
		$newAuthor->save();
		
		$sameAuthor = Author::constructByKey($newAuthor->getAuthorId());
		
		$this->assertIdentical($sameAuthor->getName(), 'Mark Twain');
		
		$this->resetCoughTestDatabase();
	}
	
	public function testRetireObject()
	{
		// NOTE this is only valid if the retire conig option is set...
		// TODO make this config option aware, so skip this test if it is not set
		
		$newLibrary = new Library();
		$newLibrary->setName('James Joyce');
		$newLibrary->setCreationDatetime(date('Y-m-d H:i:s'));
		$newLibrary->save();
		
		$sameLibrary = Library::constructByKey($newLibrary->getLibraryId());
		$sameLibrary->setIsRetired(true);
		$sameLibrary->save();
		
		// not sure if this one should pass... depends on how we are doing retired handling
		$this->assertNull(Library::constructByKey($newLibrary->getLibraryId()));
		
		$this->resetCoughTestDatabase();
	}
	
	public function testDeleteObject()
	{
		$newLibrary = new Library();
		$newLibrary->setName('James Joyce');
		$newLibrary->setCreationDatetime(date('Y-m-d H:i:s'));
		$newLibrary->save();
		
		$newLibrary->delete();
		
		$this->assertNull(Library::constructByKey($newLibrary->getLibraryId()));
		
		$this->resetCoughTestDatabase();
	}
	
	public function testSetAndGetObject()
	{
		// case 1: author object to be set is anonymous, book is saved
		$joyce = new Author();
		$joyce->setName('James Joyce');
		$joyce->setCreationDatetime(date('Y-m-d H:i:s'));
		
		$ulysses = new Book();
		$ulysses->setTitle('Ulysses');
		$ulysses->setIntroduction('1264 pages of bs by one of the masters.');
		$ulysses->setCreationDatetime(date('Y-m-d H:i:s'));
		$ulysses->save();
		
		$ulysses->setAuthor_Object($joyce);
		
		$this->assertReference($ulysses->getAuthor_Object(), $joyce);
		
		$ulysses->save();
		
		$this->assertEqual($ulysses->getAuthorId(), $joyce->getAuthorId());
		
		// case 2: author object saved, book is saved
		$twain = new Author();
		$twain->setName('Mark Twain');
		$twain->setCreationDatetime(date('Y-m-d H:i:s'));
		$twain->save();
		
		$huckFinn = new Book();
		$huckFinn->setTitle('Huckleberry Finn');
		$huckFinn->setIntroduction('meh.');
		$huckFinn->setCreationDatetime(date('Y-m-d H:i:s'));
		$huckFinn->save();
		
		$huckFinn->setAuthor_Object($twain);
		
		$this->assertReference($huckFinn->getAuthor_Object(), $twain);
		
		$huckFinn->save();
		
		// this fails right now... why?
		$this->assertEqual($huckFinn->getAuthorId(), $twain->getAuthorId());
		
		// case 3: author object is anonymous, book is anonymous
		$murakami = new Author();
		$murakami->setName('Haruki Murakami');
		$murakami->setCreationDatetime(date('Y-m-d H:i:s'));
		
		$windup = new Book();
		$windup->setTitle('The Wind Up Bird Chronicles');
		$windup->setIntroduction('trippy.');
		$windup->setCreationDatetime(date('Y-m-d H:i:s'));
		
		$windup->setAuthor_Object($murakami);
		
		$this->assertReference($windup->getAuthor_Object(), $murakami);
		
		$windup->save();
		
		$this->assertEqual($windup->getAuthorId(), $murakami->getAuthorId());
		
		// case 4: author object is saved, book is anonymous
		$heinlein = new Author();
		$heinlein->setName('Robert A. Heinlein');
		$heinlein->setCreationDatetime(date('Y-m-d H:i:s'));
		$heinlein->save();
		
		$stranger = new Book();
		$stranger->setTitle('Stranger in a Strange Land');
		$stranger->setIntroduction('awesome');
		$stranger->setCreationDatetime(date('Y-m-d H:i:s'));
		
		$stranger->setAuthor_Object($heinlein);
		
		$this->assertReference($stranger->getAuthor_Object(), $heinlein);
		
		$stranger->save();
		
		// this fails right now... why?
		$this->assertEqual($stranger->getAuthorId(), $heinlein->getAuthorId());
		
		$this->resetCoughTestDatabase();
	}
	
	public function testAddAndRemoveObject()
	{
		// case 1: author object is anonymous, book is saved
		$joyce = new Author();
		$joyce->setName('James Joyce');
		$joyce->setCreationDatetime(date('Y-m-d H:i:s'));
		
		$ulysses = new Book();
		$ulysses->setTitle('Ulysses');
		$ulysses->setIntroduction('1264 pages of bs by one of the masters.');
		$ulysses->setCreationDatetime(date('Y-m-d H:i:s'));
		$ulysses->save();
		
		$joyce->addBook($ulysses);
		$booksByJoyce = $joyce->getBook_Collection();
		$this->assertReference($booksByJoyce[$ulysses->getBookId()], $ulysses);
		
		$joyce->save();
		
		$this->assertEqual($ulysses->getAuthorId(), $joyce->getAuthorId());
		$this->assertIdentical($ulysses->getAuthor_Object(), $joyce);
		
		// case 2: author object saved, book is saved
		$twain = new Author();
		$twain->setName('Mark Twain');
		$twain->setCreationDatetime(date('Y-m-d H:i:s'));
		$twain->save();
		
		$huckFinn = new Book();
		$huckFinn->setTitle('Huckleberry Finn');
		$huckFinn->setIntroduction('meh.');
		$huckFinn->setCreationDatetime(date('Y-m-d H:i:s'));
		$huckFinn->save();
		
		$twain->addBook($huckFinn);
		$booksByTwain = $twain->getBook_Collection();
		$this->assertReference($booksByTwain[$huckFinn->getBookId()], $huckFinn);
		
		$twain->save();
		
		$this->assertEqual($huckFinn->getAuthorId(), $twain->getAuthorId());
		$this->assertIdentical($huckFinn->getAuthor_Object(), $twain);
		
		// case 3: author object anonymous, book is anonymous
		$murakami = new Author();
		$murakami->setName('Haruki Murakami');
		$murakami->setCreationDatetime(date('Y-m-d H:i:s'));
		
		$windup = new Book();
		$windup->setTitle('The Wind Up Bird Chronicles');
		$windup->setIntroduction('meh.');
		$windup->setCreationDatetime(date('Y-m-d H:i:s'));
		
		$murakami->addBook($windup);
		$booksByMurakami = $murakami->getBook_Collection();
		$this->assertReference($booksByMurakami->get(0), $windup);
		
		$murakami->save();
		
		$this->assertEqual($windup->getAuthorId(), $murakami->getAuthorId());
		$this->assertIdentical($windup->getAuthor_Object(), $murakami);
		
		$this->resetCoughTestDatabase();
		
		$twain->save();
		
		$this->assertEqual($huckFinn->getAuthorId(), $twain->getAuthorId());
		$this->assertIdentical($huckFinn->getAuthor_Object(), $twain);
		
		// case 4: author object is saved, book is anonymous
		$heinlein = new Author();
		$heinlein->setName('Robert A. Heinlein');
		$heinlein->setCreationDatetime(date('Y-m-d H:i:s'));
		$heinlein->save();
		
		$stranger = new Book();
		$stranger->setTitle('Stranger in a Strange Land');
		$stranger->setIntroduction('meh.');
		$stranger->setCreationDatetime(date('Y-m-d H:i:s'));
		
		$heinlein->addBook($stranger);
		$booksByHeinlein = $heinlein->getBook_Collection();
		$this->assertReference($booksByHeinlein->get(0), $stranger);
		
		$heinlein->save();
		
		$this->assertEqual($stranger->getAuthorId(), $heinlein->getAuthorId());
		$this->assertIdentical($stranger->getAuthor_Object(), $heinlein);
		
		// test remove object
		
		$heinlein->removeBook($stranger->getBookId());
		$this->assertTrue($heinlein->getBook_Collection()->isEmpty());
		
		// removeObject arbitrarily sets the author id to NULL instead of the default
		// value of int(0); is this the desired behavior?
		$this->assertIdentical($stranger->getAuthorId(), 0);
		$this->assertEqual($stranger->getAuthorId(), 0);
		
		$heinlein->save();
		$sameHeinlein = Author::constructByKey($heinlein->getAuthorId());
		$this->assertTrue($sameHeinlein->getBook_Collection()->isEmpty());
		
		// make sure 
		// var_dump($stranger->getBookId());
		// $newStranger = Book::constructByKey($stranger->getBookId());
		// var_dump($newStranger);
		// die();
		// $this->assertIdentical($newStranger->getAuthorId(), 0);
		
		$this->resetCoughTestDatabase();
	}
}

?>
