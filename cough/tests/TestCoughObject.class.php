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
		error_reporting(E_ALL);
		$this->includeDependencies();
		$this->setUpDatabase();
		$this->initializeDatabase();
		$this->resetCoughTestDatabase();
		$this->generateCoughTestClasses();
		$this->includeCoughTestClasses();
	}
	
	public function resetCoughTestDatabase()
	{
		foreach ($this->coughTestDbResetSql as $sql)
		{
			$this->db->query($sql);
		}
	}
	
	public function setUpDatabase()
	{
		$testDbConfig = array(
			'adapter' => 'as',
			'driver' => 'mysql',
			'host' => 'localhost',
			'db_name' => 'test_cough_object',
			'user' => 'cough_test',
			'pass' => 'cough_test',
			'port' => '3306',
			'aliases' => array('test_cough_object'),
		);
		
		CoughDatabaseFactory::addConfig($testDbConfig);
		$this->db = CoughDatabaseFactory::getDatabase('test_cough_object');
	}
	
	public function initializeDatabase()
	{
		// We have to run this sql dump one query at a time
		$this->coughTestDbResetSql = explode(';', file_get_contents(dirname(__FILE__) . '/config/db_setup.sql'));
		
		// the last element is a blank string, so get rid of it
		array_pop($this->coughTestDbResetSql);
	}
	
	public function includeDependencies()
	{
		// include Cough + dependencies; this should be the only include necessary
		require_once(dirname(dirname(dirname(__FILE__))) . '/load.inc.php');
		require_once(APP_PATH . 'as_database/load.inc.php');
	}
	
	public function generateCoughTestClasses()
	{
		// include the CoughGenerator
		require_once(dirname(dirname(dirname(__FILE__))) . '/cough_generator/load.inc.php');
		ob_start();
		$facade = new CoughGeneratorFacade();
		$facade->generate(dirname(__FILE__) . '/config/');
		ob_end_clean();
	}
	
	public function includeCoughTestClasses()
	{
		$classPath = dirname(__FILE__) . '/config/output/';
		// include Cough generated classes
		foreach (glob($classPath . 'generated/*.php') as $filename)
		{
			require_once($filename);
		}
		
		// include Cough user classes
		foreach (glob($classPath . 'concrete/*.php') as $filename)
		{
			require_once($filename);
		}
	}
	
	//////////////////////////////////////
	// Tear Down
	//////////////////////////////////////
	
	public function tearDown()
	{
		$this->emptyCoughTestDatabase();
		$this->removeGeneratedFiles();
	}
	
	public function emptyCoughTestDatabase()
	{
		$sqlCommands = explode(';', file_get_contents(dirname(__FILE__) . '/config/db_teardown.sql'));
		
		// the last element is a blank string, so get rid of it
		array_pop($sqlCommands);
		
		foreach ($sqlCommands as $sql)
		{
			$this->db->query($sql);
		}
	}
	
	public function removeGeneratedFiles()
	{
		$classPath = dirname(__FILE__) . '/config/output/';
		// include Cough generated classes
		foreach (glob($classPath . 'generated/*.php') as $filename)
		{
			unlink($filename);
		}
		
		// include Cough user classes
		foreach (glob($classPath . 'concrete/*.php') as $filename)
		{
			unlink($filename);
		}
		
		rmdir($classPath . 'generated');
		rmdir($classPath . 'concrete');
		rmdir($classPath);
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
		
		// test the most basic case (insert with no values)
		$newBook2 = new Book();
		$newBook2->save();
		
		// Check initial state of the object
		$newBook3 = new TableWithoutAutoIncrement();
		$this->assertTrue($newBook3->isNew(), 'New object should return true for isNew');
		$this->assertFalse($newBook3->isInflated(), 'New object should return false for isInflated');
		$this->assertFalse($newBook3->hasKeyId(), 'New object with PK that defaults to zero and has no auto_increment should not have a key ID');
		
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
		$this->assertIsA(Library::constructByKey($newLibrary->getLibraryId()), 'Library');
		
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
		
		// $this->assertNotEqual($ulysses->getAuthorId(), $joyce->getAuthorId());
		
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
		
		$this->assertNotEqual($huckFinn->getAuthorId(), $twain->getAuthorId());
		
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
		
		// $this->assertNotEqual($windup->getAuthorId(), $murakami->getAuthorId());
		
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
		
		$this->assertNotEqual($stranger->getAuthorId(), $heinlein->getAuthorId());
		
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
		$this->assertReference($booksByMurakami->getPosition(0), $windup);
		
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
		$this->assertReference($booksByHeinlein->getPosition(0), $stranger);
		
		$heinlein->save();
		
		$this->assertEqual($stranger->getAuthorId(), $heinlein->getAuthorId());
		$this->assertIdentical($stranger->getAuthor_Object(), $heinlein);
		
		// test remove object
		
		$heinlein->removeBook($stranger->getBookId());
		$this->assertTrue($heinlein->getBook_Collection()->isEmpty());
		
		$this->assertIdentical($stranger->getAuthorId(), 0);
		$this->assertEqual($stranger->getAuthorId(), 0);
		
		$heinlein->save();
		$sameHeinlein = Author::constructByKey($heinlein->getAuthorId());
		$this->assertTrue($sameHeinlein->getBook_Collection()->isEmpty());
		
		// TODO make sure that book id is unset now after database save
		
		$this->resetCoughTestDatabase();
	}
	
	public function testAnonymousSave()
	{
		$author = new Author();
		$author->setName('Phillip K. Dick');
		$book = new Book();
		$book->setTitle('Minority Report');
		$book2 = new Book();
		$book2->setTitle('Do Androids Dream of Electric Sheep');
		// $library = new Library();
		// $library->setName('Travis County Reader');
		$author->addBook($book);
		$author->addBook($book2);
		
		$this->assertEqual(count($author->getBook_Collection()), 2);
		
		// $book2library = new Book2library();
		// $book2library->setLibrary_Object($library);
		// $book->addBook2library($book2library);
		// $book2->addBook2library($book2library);
		
		// $this->db->getDb()->startLoggingQueries();
		
		$author->save();
		
		// $this->dump($author->getBook_Collection());
		// print_r($this->db->getDb()->getQueryLog());
		
		$this->assertNotNull($author->getAuthorId());
		
		$this->assertNotNull($book->getBookId());
		$this->assertNotNull($book->getAuthorId());
		$this->assertIdentical($book->getAuthorId(), $author->getAuthorId());
		
		$this->assertNotNull($book2->getBookId());
		$this->assertNotNull($book2->getAuthorId());
		$this->assertIdentical($book2->getAuthorId(), $author->getAuthorId());
		
		$this->resetCoughTestDatabase();
	}
	
	public function testManyToMany()
	{
		$library = new Library();
		$library->setName('Travis County Reader');
		$library->save();
		
		$library2 = new Library();
		$library2->setName('LBJ Center');
		$library2->save();
		
		$author = new Author();
		$author->setName('Haruki Murakami');
		
		$book = new Book();
		$book->setTitle('Norwegian Wood');
		$book->save();
		
		$book2 = new Book();
		$book2->setTitle('Kafka on the Shore');
		$book2->save();
		
		$author->addBook($book);
		$author->addBook($book2);
		
		$author->save();
		
		$book2library = new Book2library();
		$book2library->setBookId($book->getBookId());
		$book2library->setLibraryId($library->getLibraryId());
		$book2library->save();
		
		$book2library2 = new Book2library();
		$book2library2->setBookId($book2->getBookId());
		$book2library2->setLibraryId($library->getLibraryId());
		$book2library2->save();
		
		$book2library3 = new Book2library();
		$book2library3->setBookId($book->getBookId());
		$book2library3->setLibraryId($library2->getLibraryId());
		$book2library3->save();
		
		$book2library4 = new Book2library();
		$book2library4->setBookId($book2->getBookId());
		$book2library4->setLibraryId($library2->getLibraryId());
		$book2library4->save();
		
		// now both books should be in both libraries
		
		$sameLibrary = Library::constructByKey($library->getLibraryId());
		$bookJoinsInTravis = $sameLibrary->getBook2library_Collection();
		// $this->dump($bookJoinsInTravis);
		$this->assertEqual(count($bookJoinsInTravis), 2);
		
		// This test is similar to the 4 that are commented out below, but this shows what is different
		// AND it ignores the creation_datetime and last_modified_datetime differences b/c they are
		// intentionally not autoloaded after a save. Just manually call load() after save if you want
		// to get the updated data.
		$mismatches = $this->getFieldMismatches($bookJoinsInTravis->getPosition(0)->getBook_Object(), $book);
		
		// Unset mistmatches that we know to be intentional
		if (isset($mismatches['creation_datetime']))
		{
			unset($mismatches['creation_datetime']);
		}
		
		if (isset($mismatches['last_modified_datetime']))
		{
			unset($mismatches['last_modified_datetime']);
		}
		
		$this->assertTrue(empty($mismatches), implode("\n", $mismatches));
		
		// these fail because the $book and $book2 don't have some default values set after save like creation_datetime
		// (which is intentional; call load() after save if you want to get the updated data.)
		// $this->assertIdentical($bookJoinsInTravis->getPosition(0)->getBook_Object(), $book);
		// $this->assertIdentical($bookJoinsInTravis->getPosition(1)->getBook_Object(), $book2);

		$this->assertEqual($bookJoinsInTravis->getPosition(0)->getBook_Object()->getBookId(), $book->getBookId());
		$this->assertEqual($bookJoinsInTravis->getPosition(1)->getBook_Object()->getBookId(), $book2->getBookId());
		
		$sameLibrary2 = Library::constructByKey($library2->getLibraryId());
		$bookJoinsInLbj = $sameLibrary2->getBook2library_Collection();
		$this->assertEqual(count($bookJoinsInLbj), 2);
		
		// these fail because the $book and $book2 don't have some default values set after save like creation_datetime
		// (which is intentional; call load() after save if you want to get the updated data.)
		// $this->assertIdentical($bookJoinsInLbj->getPosition(0)->getBook_Object(), $book);
		// $this->assertIdentical($bookJoinsInLbj->getPosition(1)->getBook_Object(), $book2);

		$this->assertEqual($bookJoinsInLbj->getPosition(0)->getBook_Object()->getBookId(), $book->getBookId());
		$this->assertEqual($bookJoinsInLbj->getPosition(1)->getBook_Object()->getBookId(), $book2->getBookId());
	}
	
	public function testTransactions()
	{
		$author = new Author();
		$author->setName('Haruki Murakami');
		$this->assertTrue($author->save());
		
		$db = Author::getDb();
		$db->startTransaction();
		$db->delete('author', array(1 => 1));
		$db->rollback();
		
		$authors = new Author_Collection();
		$authors->load();
		
		$this->assertEqual(count($authors), 1);
	}
	
	public function getFieldMismatches($object1, $object2)
	{
		$mismatches = array();
		$fields1 = $object1->getFields();
		$fields2 = $object2->getFields();
		foreach ($fields1 as $key => $value)
		{
			if ($value != $fields2[$key])
			{
				ob_start();
				var_dump($value);
				$value1 = trim(ob_get_clean());
				ob_start();
				var_dump($fields2[$key]);
				$value2 = trim(ob_get_clean());
				$mismatches[$key] = 'Key "' . $key . '" mismatches: (' . $value1 . ') != (' . $value2 . ')';
			}
		}
		return $mismatches;
	}
}

?>
