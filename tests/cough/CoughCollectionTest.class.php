<?php
class CoughCollectionTest extends PHPUnit_Framework_TestCase
{
	//////////////////////////////////////
	// Set Up
	//////////////////////////////////////
	
	public static function setUpBeforeClass()
	{
		$coughRoot = dirname(dirname(dirname(__FILE__)));
		require_once($coughRoot . '/cough/load.inc.php');
		require_once(dirname(__FILE__) . '/config/SortableElement.class.php');
		require_once(dirname(__FILE__) . '/config/SortableCollection.class.php');
		
		// Also set up the the db for mysql dependent tests
		require_once(dirname(__FILE__) . '/CoughObjectTest.class.php');
		CoughObjectTest::setUpBeforeClass();
		self::generateTestData();
	}
	
	/**
	 * Some things we only need to teardown once (like delete all the generated code)
	 **/
	public static function tearDownAfterClass()
	{
		CoughObjectTest::removeGeneratedFiles();
	}
	
	public static function generateTestData()
	{
		$twain = new Author();
		$twain->setName('Mark Twain');
		$twain->setCreationDatetime(date('Y-m-d H:i:s'));
		$twain->save();
		
		$joyce = new Author();
		$joyce->setName('James Joyce');
		$joyce->setCreationDatetime(date('Y-m-d H:i:s'));
		$joyce->save();
		
		$ulysses = new Book();
		$ulysses->setTitle('Ulysses');
		$ulysses->setIntroduction('1264 pages of bs by one of the masters.');
		$ulysses->setAuthorId($joyce->getAuthorId());
		$ulysses->setCreationDatetime(date('Y-m-d H:i:s'));
		$ulysses->save();
		
		$huckFinn = new Book();
		$huckFinn->setTitle('Huckleberry Finn');
		$huckFinn->setIntroduction('meh.');
		$huckFinn->setAuthorId($twain->getAuthorId());
		$huckFinn->setCreationDatetime(date('Y-m-d H:i:s'));
		$huckFinn->save();
		
		$tomSawyer = new Book();
		$tomSawyer->setTitle('Tom Sawyer');
		$tomSawyer->setIntroduction('meh.');
		$tomSawyer->setAuthorId($twain->getAuthorId());
		$tomSawyer->setCreationDatetime(date('Y-m-d H:i:s'));
		$tomSawyer->save();
		
		$awbush = new Person();
		$awbush->setFirstName('Anthony');
		$awbush->setFirstName('Bush');
		$awbush->save();
		
		$lzhang = new Person();
		$lzhang->setFirstName('Lewis');
		$lzhang->setFirstName('Zhang');
		$lzhang->save();
		
		$pistole = new Person();
		$pistole->setFirstName('Richard');
		$pistole->setFirstName('Pistole');
		$pistole->save();
		
		$tom = new Person();
		$tom->setFirstName('Tom');
		$tom->setFirstName('Warmbrodt');
		$tom->save();
	}
	
	//////////////////////////////////////
	// Test Methods
	//////////////////////////////////////
	
	public function buildSortableCollection()
	{
		// Data
		$sortableData = array(
			array(
				'element_id' => 1,
				'manufacturer_name' => 'Manuf A',
				'product_name' => 'Product A',
				'price' => 10.00,
			),
			array(
				'element_id' => 2,
				'manufacturer_name' => 'Manuf B',
				'product_name' => 'Product B',
				'price' => 11.00,
			),
			array(
				'element_id' => 3,
				'manufacturer_name' => 'Manuf C',
				'product_name' => 'Product C',
				'price' => 12.00,
			),
			array(
				'element_id' => 4,
				'manufacturer_name' => 'Manuf B',
				'product_name' => 'Product A',
				'price' => 22.00,
			),
		);
		
		// Create the collection
		$collection = new SortableCollection();
		$collection->add(new SortableElement($sortableData[0]));
		$collection->add(new SortableElement($sortableData[1]));
		$collection->add(new SortableElement($sortableData[2]));
		$collection->add(new SortableElement($sortableData[3]));
		
		return $collection;
	}
	
	public function testSortByMethod()
	{
		// Data
		$sortableData = array(
			array(
				'element_id' => 1,
				'manufacturer_name' => 'Manuf A',
				'product_name' => 'Product A',
				'price' => 10.00,
			),
			array(
				'element_id' => 2,
				'manufacturer_name' => 'Manuf B',
				'product_name' => 'Product B',
				'price' => 11.00,
			),
			array(
				'element_id' => 3,
				'manufacturer_name' => 'Manuf C',
				'product_name' => 'Product C',
				'price' => 12.00,
			),
		);
		
		$sortedCollectionIds = array(1,2,3);
		
		// Create a sorted copy and test that it matches before and after sorting
		$sortMe1 = new SortableCollection();
		$sortMe1->add(new SortableElement($sortableData[0]));
		$sortMe1->add(new SortableElement($sortableData[1]));
		$sortMe1->add(new SortableElement($sortableData[2]));
		$this->assertEquals($sortedCollectionIds, $sortMe1->getArrayKeys());
		$sortMe1->sortByMethod('getManufacturerName');
		$this->assertEquals($sortedCollectionIds, $sortMe1->getArrayKeys());
		unset($sortMe1);
		
		// Create another sorted copy and sort it using another method
		$sortMe2 = new SortableCollection();
		$sortMe2->add(new SortableElement($sortableData[0]));
		$sortMe2->add(new SortableElement($sortableData[1]));
		$sortMe2->add(new SortableElement($sortableData[2]));
		$sortMe2->sortByMethod('getProductName');
		$this->assertEquals($sortedCollectionIds, $sortMe2->getArrayKeys());
		unset($sortMe2);
		
		// Create another sorted copy and sort it using another method
		$sortMe3 = new SortableCollection();
		$sortMe3->add(new SortableElement($sortableData[0]));
		$sortMe3->add(new SortableElement($sortableData[1]));
		$sortMe3->add(new SortableElement($sortableData[2]));
		$sortMe3->sortByMethod('getPrice');
		$this->assertEquals($sortedCollectionIds, $sortMe3->getArrayKeys());
		unset($sortMe3);
		
		///////////////////////////////////////////
		// Now create unsorted copies and sort them
		///////////////////////////////////////////
		
		// Create an unsorted copy and sort it
		$sortMe4 = new SortableCollection();
		$sortMe4->add(new SortableElement($sortableData[1]));
		$sortMe4->add(new SortableElement($sortableData[2]));
		$sortMe4->add(new SortableElement($sortableData[0]));
		$this->assertNotEquals($sortedCollectionIds, $sortMe4->getArrayKeys());
		$sortMe4->sortByMethod('getManufacturerName');
		$this->assertEquals($sortedCollectionIds, $sortMe4->getArrayKeys());
		unset($sortMe4);
		
		// Create another unsorted copy and sort it using another method
		$sortMe5 = new SortableCollection();
		$sortMe5->add(new SortableElement($sortableData[1]));
		$sortMe5->add(new SortableElement($sortableData[2]));
		$sortMe5->add(new SortableElement($sortableData[0]));
		$this->assertNotEquals($sortedCollectionIds, $sortMe5->getArrayKeys());
		$sortMe5->sortByMethod('getProductName');
		$this->assertEquals($sortedCollectionIds, $sortMe5->getArrayKeys());
		unset($sortMe5);
		
		// Create another unsorted copy and sort it using another method
		$sortMe6 = new SortableCollection();
		$sortMe6->add(new SortableElement($sortableData[1]));
		$sortMe6->add(new SortableElement($sortableData[2]));
		$sortMe6->add(new SortableElement($sortableData[0]));
		$this->assertNotEquals($sortedCollectionIds, $sortMe6->getArrayKeys());
		$sortMe6->sortByMethod('getProductName');
		$this->assertEquals($sortedCollectionIds, $sortMe6->getArrayKeys());
		unset($sortMe6);
		
	}
	
	public function testSortByMethods()
	{
		$collection = $this->buildSortableCollection();
		
		$collection->sortByMethods('getManufacturerName');
		$this->assertEquals(array(1,4,2,3), $collection->getArrayKeys());
		
		$collection->sortByMethods('getManufacturerName', SORT_DESC);
		$this->assertEquals(array(3,2,4,1), $collection->getArrayKeys());
		
		$collection->sortByMethods('getManufacturerName', 'getProductName');
		$this->assertEquals(array(1,4,2,3), $collection->getArrayKeys());
		
		$collection->sortByMethods('getManufacturerName', 'getProductName', SORT_DESC);
		$this->assertEquals(array(1,2,4,3), $collection->getArrayKeys());
		
		$collection->sortByMethods('getManufacturerName', SORT_DESC, 'getProductName', SORT_DESC);
		$this->assertEquals(array(3,2,4,1), $collection->getArrayKeys());
		
		$collection->sortByMethods('getProductName', 'getManufacturerName');
		$this->assertEquals(array(1,4,2,3), $collection->getArrayKeys());
		
		$collection->sortByMethods('getProductName', 'getManufacturerName', SORT_DESC);
		$this->assertEquals(array(4,1,2,3), $collection->getArrayKeys());
		
		$collection->sortByMethods('getProductName', SORT_DESC, 'getManufacturerName', SORT_DESC);
		$this->assertEquals(array(3,2,4,1), $collection->getArrayKeys());
		
		$collection->sortByMethods('getPrice');
		$this->assertEquals(array(1,2,3,4), $collection->getArrayKeys());
		
		$collection->sortByMethods('getPrice', SORT_DESC);
		$this->assertEquals(array(4,3,2,1), $collection->getArrayKeys());
		
		$collection->sortByMethods('getPrice', SORT_ASC, 'getProductName', SORT_DESC, 'getManufacturerName');
		$this->assertEquals(array(1,2,3,4), $collection->getArrayKeys());
		
		
		// test sort empty collection
		$emptyCollection = new SortableCollection();
		$collection->sortByMethods('getManufacturerName');


		// test sort empty collection
		$threwException = false; 
		try 
		{
			$collection->sortByMethods();
		}
		catch (Exception $e)
		{
			$threwException = true;
		}
		
		$this->assertTrue($threwException);
		
	}
	
	public function testSortByKeys()
	{
		$collection = $this->buildSortableCollection();
		
		$keyPermutations = array(
			array(1,2,3,4),
			array(1,2,4,3),
			
			array(1,3,2,4),
			array(1,3,4,2),
			
			array(1,4,2,3),
			array(1,4,3,2),
			
			array(2,1,3,4),
			array(2,1,4,3),
			
			array(2,3,1,4),
			array(2,3,4,1),
			
			array(2,4,1,3),
			array(2,4,3,1),
			
			array(3,1,2,4),
			array(3,1,4,2),
			
			array(3,2,1,4),
			array(3,2,4,1),
			
			array(3,4,1,2),
			array(3,4,2,1),
			
			array(4,1,2,3),
			array(4,1,3,2),
			
			array(4,2,1,3),
			array(4,2,3,1),
			
			array(4,3,1,2),
			array(4,3,2,1),
		);
		
		foreach ($keyPermutations as $keyPermutation)
		{
			$collection->sortByKeys($keyPermutation);
			$this->assertEquals($keyPermutation, $collection->getArrayKeys());
		}
	}
	
	public function testGetKeyValueIteratorWithGetKeyId()
	{
		$collection = $this->buildSortableCollection();
		
		// Lack of a second parameter to `getKeyValueIterator()` should mean the
		// `getKeyId()` method is used on every object.

		$manualData = array();
		foreach ($collection as $keyId => $element)
		{
			$manualData[$keyId] = $element->getProductName();
		}
		
		$iterator = $collection->getKeyValueIterator('getProductName');
		$iteratorData = array();
		foreach ($iterator as $keyId => $productName)
		{
			$iteratorData[$keyId] = $productName;
		}
		
		$this->assertSame($manualData, $iteratorData);
		
		// count should work
		$this->assertSame(count($manualData), count($iterator));
		$this->assertSame(count($collection), count($iterator));
		
		// empty should work
		$this->assertSame(empty($manualData), empty($iterator));
		$this->assertSame(empty($collection), empty($iterator));
	}
	
	public function testGetKeyValueIteratorWithCustomKeyId()
	{
		$collection = $this->buildSortableCollection();
		
		// Specifying the second parameter should work
		
		$manualData = array();
		foreach ($collection as $element)
		{
			$manualData[$element->getPrice()] = $element->getProductName();
		}
		
		$iterator = $collection->getKeyValueIterator('getProductName', 'getPrice');
		$iteratorData = array();
		foreach ($iterator as $keyId => $productName)
		{
			$iteratorData[$keyId] = $productName;
		}
		
		$this->assertSame($manualData, $iteratorData);
		
		// count should work
		$this->assertSame(count($manualData), count($iterator));
		$this->assertSame(count($collection), count($iterator));
		
		// empty should work
		$this->assertSame(empty($manualData), empty($iterator));
		$this->assertSame(empty($collection), empty($iterator));
	}
	
	public function testCanRemoveItemsFromCollectionWhileLooping()
	{
		$collection = $this->buildSortableCollection();
		$initialCount = count($collection);
		
		$this->assertTrue($initialCount > 1, 'This test needs a collection size greater than one.');
		
		foreach ($collection as $element)
		{
			$collection->remove($element);
			// $collection->offsetUnset($element->getKeyId());
			// unset($collection[$element->getKeyId()]);
		}
		
		$this->assertTrue(count($collection) == 0);
	}
	
	public function testLoadByHashWithSingleField()
	{
		$twain = Author::constructByKey(array(
			'name' => 'Mark Twain',
		));
		
		$books = new Book_Collection();
		$books->loadByHash(array(
			'author_id' => $twain->getAuthorId(),
		));
		
		$this->assertSame(count($books), 2);
		
		foreach ($books as $book)
		{
			$this->assertSame($book->getAuthorId(), $twain->getAuthorId());
		}
	}
	
	public function testLoadByHashWithSqlFunction()
	{
		$huckFinn = Book::constructByKey(array(
			'title' => 'Huckleberry Finn',
		));
		
		$books = new Book_Collection();
		$books->loadByHash(array(
			'title' => new As_SqlFunction('TRIM(' . Book::getDb()->quote($huckFinn->getTitle()) . ')'),
		));
		
		$this->assertSame(count($books), 1);
		
		foreach ($books as $book)
		{
			$this->assertSame($book->getBookId(), $huckFinn->getBookId());
		}
	}
	
	public function testLoadByHashWithMultipleFields()
	{
		$twain = Author::constructByKey(array(
			'name' => 'Mark Twain',
		));
		
		$huckFinn = Book::constructByKey(array(
			'title' => 'Huckleberry Finn',
		));
		
		$books = new Book_Collection();
		$books->loadByHash(array(
			'author_id' => $twain->getAuthorId(),
			'title' => 'Huckleberry Finn',
		));
		
		$this->assertSame(count($books), 1);
		
		foreach ($books as $book)
		{
			$this->assertSame($book->getAuthorId(), $twain->getAuthorId());
			$this->assertSame($book->getBookId(), $book->getBookId());
		}
	}
	
	public function testLoadByHashWithEmptyFields()
	{
		$books = new Book_Collection();
		$books->loadByHash(array());
		$this->assertTrue($books->isEmpty());
		
		$books->loadByHash(null);
		$this->assertTrue($books->isEmpty());
		
		$books->loadByHash('');
		$this->assertTrue($books->isEmpty());
	}
	
	public function testLoadByHashWithSqlObjectLoad()
	{
		include_once(dirname(__FILE__) . '/config/SqlObjectBook.class.php');
		include_once(dirname(__FILE__) . '/config/SqlObjectBook_Collection.class.php');
		
		$twain = Author::constructByKey(array(
			'name' => 'Mark Twain',
		));
		
		$huckFinn = Book::constructByKey(array(
			'title' => 'Huckleberry Finn',
		));
		
		$books = new SqlObjectBook_Collection();
		$books->loadByHash(array(
			'author_id' => $twain->getAuthorId(),
			'title' => 'Huckleberry Finn',
		));
		
		$this->assertSame(count($books), 1);
		
		foreach ($books as $book)
		{
			$this->assertSame($book->getAuthorId(), $twain->getAuthorId());
			$this->assertSame($book->getBookId(), $book->getBookId());
		}
	}
	
	public function testLoadByIds()
	{
		$twain = Author::constructByKey(array(
			'name' => 'Mark Twain',
		));
		
		$joyce = Author::constructByKey(array(
			'name' => 'James Joyce',
		));
		
		$authors = new Author_Collection();
		$authors->loadByIds(array(
			$twain->getAuthorId(),
			$joyce->getAuthorId(),
		));
		
		$this->assertSame(count($authors), 2);
		
		foreach ($authors as $author)
		{
			switch ($author->getName())
			{
				case 'Mark Twain':
					$this->assertSame($author->getAuthorId(), $twain->getAuthorId());
					break;
				
				case 'James Joyce':
					$this->assertSame($author->getAuthorId(), $joyce->getAuthorId());
					break;
				
				default:
					$this->fail($author->getName() . " isn't supposed to be loaded into the collection.");
					break;
			}
		}
	}
	
	public function testLoadByIdsWithFieldName()
	{
		$huckFinn = Book::constructByKey(array(
			'title' => 'Huckleberry Finn',
		));
		
		$ulysses = Book::constructByKey(array(
			'title' => 'Ulysses',
		));
		
		$books = new Book_Collection();
		$books->loadByIds(array(
			$huckFinn->getTitle(),
			$ulysses->getTitle(),
		), 'title');
		
		$this->assertSame(count($books), 2);
		
		foreach ($books as $book)
		{
			switch ($book->getTitle())
			{
				case 'Huckleberry Finn':
					$this->assertSame($book->getBookId(), $huckFinn->getBookId());
					break;
				
				case 'Ulysses':
					$this->assertSame($book->getBookId(), $ulysses->getBookId());
					break;
				
				default:
					$this->fail($book->getTitle() . " isn't supposed to be loaded into the collection.");
					break;
			}
		}
	}
	
	public function testLoadByIdsWithSqlFunction()
	{
		$twain = Author::constructByKey(array(
			'name' => 'Mark Twain',
		));
		
		$joyce = Author::constructByKey(array(
			'name' => 'James Joyce',
		));
		
		$authors = new Author_Collection();
		$authors->loadByIds(array(
			new As_SqlFunction('TRIM(' . Author::getDb()->quote(' ' . $twain->getName()) . ')'),
			new As_SqlFunction('TRIM(' . Author::getDb()->quote(' ' . $joyce->getName()) . ')'),
		), 'name');
		
		$this->assertSame(count($authors), 2);
		
		foreach ($authors as $author)
		{
			switch ($author->getName())
			{
				case 'Mark Twain':
					$this->assertSame($author->getAuthorId(), $twain->getAuthorId());
					break;
				
				case 'James Joyce':
					$this->assertSame($author->getAuthorId(), $joyce->getAuthorId());
					break;
				
				default:
					$this->fail($author->getName() . " isn't supposed to be loaded into the collection.");
					break;
			}
		}
	}
	
	public function testLoadByIdsWithSqlObjectLoad()
	{
		include_once(dirname(__FILE__) . '/config/SqlObjectBook.class.php');
		include_once(dirname(__FILE__) . '/config/SqlObjectBook_Collection.class.php');
		
		$huckFinn = Book::constructByKey(array(
			'title' => 'Huckleberry Finn',
		));
		
		$ulysses = Book::constructByKey(array(
			'title' => 'Ulysses',
		));
		
		$books = new SqlObjectBook_Collection();
		$books->loadByIds(array(
			$huckFinn->getBookId(),
			$ulysses->getBookId(),
		));
		
		$this->assertSame(count($books), 2);
		
		foreach ($books as $book)
		{
			switch ($book->getTitle())
			{
				case 'Huckleberry Finn':
					$this->assertSame($book->getAuthorId(), $huckFinn->getAuthorId());
					break;
				
				case 'Ulysses':
					$this->assertSame($book->getAuthorId(), $ulysses->getAuthorId());
					break;
				
				default:
					$this->fail($author->getName() . " isn't supposed to be loaded into the collection.");
					break;
			}
		}
	}
	
	public function testLoadByIdsWithEmptyIds()
	{
		$authors = new Author_Collection();
		
		$authors->loadByIds(array());
		$this->assertTrue($authors->isEmpty());
		
		$authors->loadByIds('');
		$this->assertTrue($authors->isEmpty());
		
		$authors->loadByIds(null);
		$this->assertTrue($authors->isEmpty());
	}
	
	public function testLoadByIdsWithNoPk()
	{
		include_once(dirname(__FILE__) . '/config/NoPkBook.class.php');
		include_once(dirname(__FILE__) . '/config/NoPkBook_Collection.class.php');
		$books = new NoPkBook_Collection();
		
		try
		{
			$books->loadByIds(array(1, 2, 3));
		}
		catch (CoughException $e)
		{
			$this->assertSame($e->getMessage(), 'Unable to load by ids without one and only one primary key or explicit field name');
			return;
		}
		
		$this->fail('An expected exception has not been raised.');
	}
	
	public function testLoadByPreparedStmt()
	{
		$huckFinn = Book::constructByKey(array(
			'title' => 'Huckleberry Finn',
		));
		
		$books = new Book_Collection();
		$books->loadByPreparedStmt('SELECT * FROM book INNER JOIN author USING (author_id) WHERE author.name = ? AND book.title = ?', array(
			'Mark Twain',
			'Huckleberry Finn',
		));
		
		$this->assertSame(count($books), 1);
		
		foreach ($books as $book)
		{
			$this->assertEquals($book->getBookId(), $huckFinn->getBookId());
		}
	}
	
	public function testLoadByPreparedStmtWithTypes()
	{
		$twain = Author::constructByKey(array(
			'name' => 'Mark Twain',
		));
		
		$huckFinn = Book::constructByKey(array(
			'title' => 'Huckleberry Finn',
		));
		
		$books = new Book_Collection();
		$books->loadByPreparedStmt('SELECT * FROM book WHERE book.author_id = ? AND book.title = ?', array(
			$twain->getAuthorId(),
			'Huckleberry Finn',
		), 'is');
		
		$this->assertSame(count($books), 1);
		
		foreach ($books as $book)
		{
			$this->assertEquals($book->getBookId(), $huckFinn->getBookId());
		}
	}
	
	public function testGetPosition()
	{
		$books = new Book_Collection();
		$this->assertSame($books->getPosition(0), null);
		$books->load();
		
		$books->sortByMethod('getTitle', SORT_DESC);
		$this->assertSame($books->getPosition(0)->getTitle(), 'Ulysses');
		$this->assertSame($books->getPosition(1)->getTitle(), 'Tom Sawyer');
		$this->assertSame($books->getPosition(2)->getTitle(), 'Huckleberry Finn');
		$this->assertSame($books->getPosition(3), null);
	}
	
	public function testGetFirstAndGetLast()
	{
		$twain = Author::constructByKey(array(
			'name' => 'Mark Twain',
		));
		
		$books = new Book_Collection();
		
		$this->assertSame($books->getFirst(), null);
		$this->assertSame($books->getLast(), null);
		
		$books->loadByHash(array(
			'author_id' => $twain->getAuthorId(),
		));
		
		$books->sortByMethod('getTitle', SORT_ASC);
		
		$this->assertSame($books->getFirst()->getTitle(), 'Huckleberry Finn');
		$this->assertSame($books->getLast()->getTitle(), 'Tom Sawyer');
	}
	
	public function testIsEmpty()
	{
		$books = new Book_Collection();
		$this->assertTrue($books->isEmpty());
		$books->add(Book::constructByKey(array(
			'title' => 'Huckleberry Finn',
		)));
		$this->assertFalse($books->isEmpty());
		$books->remove($books->getFirst());
		$this->assertTrue($books->isEmpty());
	}
	
	public function testGet()
	{
		$huckFinn = Book::constructByKey(array(
			'title' => 'Huckleberry Finn',
		));
		
		$ulysses = Book::constructByKey(array(
			'title' => 'Ulysses',
		));
		
		$tomSawyer = Book::constructByKey(array(
			'title' => 'Tom Sawyer',
		));
		
		// anonymous object with no key id
		$finnegans = new Book();
		$finnegans->setTitle('Finnegans Wake');
		
		$books = new Book_Collection();
		$books->add($huckFinn);
		$books->add($ulysses);
		$books->add($finnegans);
		
		$this->assertSame($huckFinn, $books->get($huckFinn->getBookId()));
		$this->assertSame($huckFinn, $books->get($huckFinn));
		$this->assertSame($finnegans, $books->get($finnegans));
		$this->assertSame($ulysses, $books->get(array('book_id' => $ulysses->getBookId())));
		$this->assertNull($books->get($tomSawyer->getBookId()));
		$this->assertNull($books->get($tomSawyer));
	}
	
	public function testRemove()
	{
		$huckFinn = Book::constructByKey(array(
			'title' => 'Huckleberry Finn',
		));
		
		$ulysses = Book::constructByKey(array(
			'title' => 'Ulysses',
		));
		
		$tomSawyer = Book::constructByKey(array(
			'title' => 'Tom Sawyer',
		));
		
		// anonymous object with no key id
		$finnegans = new Book();
		$finnegans->setTitle('Finnegans Wake');
		
		$books = new Book_Collection();
		$this->assertSame($huckFinn, $books->add($huckFinn));
		$this->assertSame($ulysses, $books->add($ulysses));
		$this->assertSame($finnegans, $books->add($finnegans));
		
		$this->assertSame($huckFinn, $books->remove($huckFinn));
		$this->assertNull($books->get($huckFinn));
		
		$this->assertSame($ulysses, $books->remove($ulysses->getBookId()));
		$this->assertNull($books->get($ulysses));
		
		$this->assertSame($finnegans, $books->remove($finnegans));
		$this->assertNull($books->get($finnegans));
		
		$this->assertTrue($books->isEmpty());
		
		$this->assertFalse($books->remove($huckFinn));
		$this->assertFalse($books->remove($ulysses->getBookId()));
		$this->assertFalse($books->remove($finnegans));
	}
	
	public function testRemoveFailure()
	{
		include_once(dirname(__FILE__) . '/config/UnsaveableBook.class.php');
		include_once(dirname(__FILE__) . '/config/UnsaveableBook_Collection.class.php');
		
		$book = new UnsaveableBook();
		$books = new UnsaveableBook_Collection();
		$books->add($book);
		$books->remove($book);
		$this->assertFalse($books->save());
	}
	
	public function testSaveFailure()
	{
		include_once(dirname(__FILE__) . '/config/UnsaveableBook.class.php');
		include_once(dirname(__FILE__) . '/config/UnsaveableBook_Collection.class.php');
		
		$books = new UnsaveableBook_Collection();
		$books->add(new UnsaveableBook());
		$this->assertFalse($books->save());
	}
}
