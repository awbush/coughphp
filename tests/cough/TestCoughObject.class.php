<?php

/**
 * @todo consider switching to PHPUnit, especially if it has PHP5 strict compatibility.
 * @todo consider splitting out some of the test methods, and rename them in a
 * way that indicates requirements rather than what it is doing.  The
 * TestAutoloader.class.php file is a good example.  Once we do this, we can also
 * benefit from concepts like Agile Documentation {@link http://www.phpunit.de/manual/3.3/en/other-uses-for-tests.html}
 **/
class TestCoughObject extends UnitTestCase
{
	protected $db = null; // the database object
	protected $coughTestDbResetSql = '';
	
	//////////////////////////////////////
	// Set Up
	//////////////////////////////////////
	
	/**
	 * Some things we only need to setup once (like generate all the code from the DB)
	 **/
	public function __construct()
	{
		error_reporting(E_ALL);
		$this->includeDependencies();
		$this->setUpDatabase();
		$this->loadSetupSql();
		$this->resetCoughTestDatabase();
		$this->generateCoughTestClasses();
		$this->includeCoughTestClasses();
	}
	
	/**
	 * Some things we only need to teardown once (like delete all the generated code)
	 **/
	public function __destruct()
	{
		$this->removeGeneratedFiles();
	}
	
	/**
	 * This method is run by simpletest before running each test*() method.
	 *
	 * @return void
	 **/
	public function setUp()
	{
		$this->resetCoughTestDatabase();
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
		// Use connection information from the generation config (and just add the aliases)
		include(dirname(__FILE__) . '/config/database_schema_generator.inc.php');
		
		$dbName = $config['dsn']['db_name'];
		$testDbConfig = $config['dsn'];
		$testDbConfig['aliases'] = array($dbName);
		
		CoughDatabaseFactory::addConfig($testDbConfig);
		$this->db = CoughDatabaseFactory::getDatabase($dbName);
	}
	
	public function loadSetupSql()
	{
		// We have to run this sql dump one query at a time
		$this->coughTestDbResetSql = explode(';', file_get_contents(dirname(__FILE__) . '/config/db_setup.sql'));
		
		// the last element is a blank string, so get rid of it
		array_pop($this->coughTestDbResetSql);
	}
	
	public function includeDependencies()
	{
		// include Cough + dependencies; this should be the only include necessary
		$coughRoot = dirname(dirname(dirname(__FILE__)));
		require_once($coughRoot . '/cough/load.inc.php');
		require_once($coughRoot . '/as_database/load.inc.php');
		require_once($coughRoot . '/cough_generator/load.inc.php');
	}
	
	public function generateCoughTestClasses()
	{
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
	}
	
	public function testSetFieldsThroughSetters()
	{
		$data = array(
			'title' => 'Ulysses',
			'introduction' => '1264 pages of bs by one of the masters.',
			'creation_datetime' => date('Y-m-d H:i:s'),
		);
		$newBook = new Book();
		$newBook->setFieldsThroughSetters($data);
		$this->assertTrue($newBook->save());
	}
	
	public function testGetFieldsThroughGetters()
	{
		include_once(dirname(__FILE__) . '/config/Book2.class.php');
		
		$data = array(
			'title' => 'Ulysses',
			'introduction' => '1264 pages of bs by one of the masters.',
			'creation_datetime' => date('Y-m-d H:i:s'),
		);
		
		$book = new Book2($data);
		$getterData = $book->getFieldsThroughGetters();
		
		$this->assertEqual('Your title is mine now!', $getterData['title']);
		$this->assertEqual($data['introduction'], $getterData['introduction']);
		$this->assertEqual($data['creation_datetime'], $getterData['creation_datetime']);
	}
	
	public function testSetKeyId()
	{
		// calling setKeyId should be the same as calling the explicit setters for the columns making up the primary key.
		$book1 = new Book();
		$book1->setKeyId(1);
		$book2 = new Book();
		$book2->setBookId(1);
		$this->assertEqual($book1->getFields(), $book2->getFields());
		
		// do it again, but this time check the entire object state (this means we can't notify children of the change)
		$book1 = new Book();
		$book1->setKeyId(1, false); // false => don't notify children of key change
		$book2 = new Book();
		$book2->setBookId(1);
		$this->assertEqual(serialize($book1), serialize($book2));
	}
	
	public function testHasKeyId()
	{
		// after setting a key ID on a new object, it shall still be considered new but shall be aware of it's key ID.
		$book = new Book();
		$book->setKeyId(1);
		$this->assertTrue($book->hasKeyId(), 'New object should be aware of its key when it was explicitly set via setKeyId.');
		$this->assertTrue($book->isNew(), 'New object should still be new after its key was explicitly set via setKeyId.');
		
		// make sure it works when calling the explicit setters for the columns making up the primary key.
		$book = new Book();
		$book->setBookId(1);
		$this->assertTrue($book->hasKeyId(), 'New object should be aware of its key when it was explicitly set via setters.');
		$this->assertTrue($book->isNew(), 'New object should still be new after its key was explicitly set via setters.');
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
	}
	
	public function testRetireObject()
	{
		$newLibrary = new Library();
		$newLibrary->setName('James Joyce');
		$newLibrary->setCreationDatetime(date('Y-m-d H:i:s'));
		$newLibrary->save();
		
		$sameLibrary = Library::constructByKey($newLibrary->getLibraryId());
		$sameLibrary->setIsRetired(true);
		$sameLibrary->save();
		
		// constructByKey should always return the object, even if we add "retired"
		// handling. It's mostly meant as a way to keep items from showing up in related
		// collections, NOT to keep the entitity from being retrieved (just delete it in
		// that case).
		$this->assertIsA(Library::constructByKey($newLibrary->getLibraryId()), 'Library');
	}
	
	public function testDeleteObject()
	{
		$newLibrary = new Library();
		$newLibrary->setName('James Joyce');
		$newLibrary->setCreationDatetime(date('Y-m-d H:i:s'));
		$newLibrary->save();
		
		$newLibrary->delete();
		
		$this->assertNull(Library::constructByKey($newLibrary->getLibraryId()));
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
