<?php

// To test the generator we'll need several schema's and configurations. Let's organize like so:

// tests/ <- folder this file is in
//    test_configs/
//        config_name/
//            db_setup.sql <- SQL to setup all the tables in the database.
//            db_teardown.sql <- SQL to drop everything that was setup in above file.
//            cough_generator.inc.php <- generator config that will be tested
//            database_schema_generator.inc.php <- database config that will be tested

// Order of operations for each test config:
// 1. run the db_setup.sql
// 2. run the generator to get the classes, and write them to disk.
// 3. test the generated results
// 5. delete the written classes
// 6. run the db_teardown.sql

class TestCoughGenerator extends UnitTestCase
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
		$this->includeDependencies();
		$this->setUpDatabaseConnection();
	}
	
	public function includeDependencies()
	{
		// include CoughGenerator so we can perform the generation steps.
		require_once(dirname(dirname(__FILE__)) . '/load.inc.php');
		
		// include Cough so we can include and test the generated classes.
		require_once(dirname(dirname(dirname(__FILE__))) . '/load.inc.php');
	}
	
	public function setUpDatabaseConnection()
	{
		$testDbConfig = array(
			'driver' => 'mysql',
			'host' => 'localhost',
			'db_name' => 'test_cough_object',
			'user' => 'cough_test',
			'pass' => 'cough_test',
			'port' => '3306'
		);
		
		CoughDatabaseFactory::addConfig('test_cough_object', $testDbConfig);
		$this->db = CoughDatabaseFactory::getDatabase('test_cough_object');
	}
	
	public function executeSqlFile($sqlFile)
	{
		// We have to run this sql dump one query at a time
		$sqlCommands = explode(';', file_get_contents($sqlFile));
		
		// the last element is a blank string, so get rid of it
		array_pop($sqlCommands);

		foreach ($sqlCommands as $sql) {
			$this->db->query($sql);
		}
	}
	
	public function includeGeneratedClasses($configName)
	{
		$classPath = dirname(__FILE__) . '/test_configs/' . $configName . '/output/';
		
		// include Cough generated classes
		foreach (glob($classPath . 'generated/*.php') as $filename) {
			require_once($filename);
		}
		
		// include Cough user classes
		foreach (glob($classPath . 'concrete/*.php') as $filename) {
			require_once($filename);
		}
	}
	
	public function removeGeneratedClasses($configName)
	{
		$classPath = dirname(__FILE__) . '/test_configs/' . $configName . '/output/';
		
		// remove Cough generated classes
		foreach (glob($classPath . 'generated/*.php') as $filename) {
			unlink($filename);
		}
		
		// remove Cough user classes
		foreach (glob($classPath . 'concrete/*.php') as $filename) {
			unlink($filename);
		}
		
		rmdir($classPath . 'generated');
		rmdir($classPath . 'concrete');
		rmdir($classPath);
	}
	
	//////////////////////////////////////
	// Tear Down
	//////////////////////////////////////
	
	public function tearDown()
	{
	}

	//////////////////////////////////////
	// Tests...
	//////////////////////////////////////
	
	public function testConfig1()
	{
		$configName = 'test_config1';
		$configPath = dirname(__FILE__) . '/test_configs/' . $configName . '/';
		
		// Setup the database
		$this->executeSqlFile($configPath . 'db_setup.sql');
		
		// Which config to use?
		$schemaGeneratorConfigFile = $configPath . 'database_schema_generator.inc.php';
		$coughGeneratorConfigFile  = $configPath . 'cough_generator.inc.php';

		// Get the database config
		$schemaGeneratorConfig = DatabaseSchemaGeneratorConfig::constructFromFile($schemaGeneratorConfigFile);

		// Load the schema into memory
		$schemaGenerator = new DatabaseSchemaGenerator($schemaGeneratorConfig);
		$schema = $schemaGenerator->generateSchema();

		// Manipulate the schema (to add any missed relationships, e.g.)
		$manipulator = new SchemaManipulator($schemaGeneratorConfig);
		$manipulator->manipulateSchema($schema);

		// Get the cough generator config
		$coughGeneratorConfig = CoughGeneratorConfig::constructFromFile($coughGeneratorConfigFile);

		// Generate files into memory
		$coughGenerator = new CoughGenerator($coughGeneratorConfig);
		$classes = $coughGenerator->generateCoughClasses($schema);
		
		// Write files to disk
		$coughWriter = new CoughWriter($coughGeneratorConfig);
		$this->assertTrue($coughWriter->writeClasses($classes), 'Unable to write classes to disk.');
		
		// Start asserting
		
		// Test that the correct number of classes were generated
		$numClassesPerTable = 4;
		$numTables = 4;
		$expectedNumClasses = $numClassesPerTable * $numTables;
		$this->assertEqual(count($classes), $expectedNumClasses, 'Incorrect number of classes generated (' .  count($classes) . ' where generated, but ' . $expectedNumClasses . ' where expected).');
		
		// Test that we generated for the correct tables
		$tables = array('author', 'book', 'book2library', 'library');
		$tablesGeneratedFor = array();
		foreach ($classes as $class) {
			$tablesGeneratedFor[] = $class->getTableName();
		}
		$this->assertEqual(asort($tables), asort($tablesGeneratedFor), 'Did not generate for exactly the tables in question.');
		
		// Test that we got all the expected names for each class.
		$expectedClassNamesAndMethods = array(
			'Author' => array(
				'constructByKey',
				'constructBySql',
				'constructByFields',
				'getLoadSql'
			),
			'Author_Generated' => array(
				'notifyChildrenOfKeyChange',
				'getAuthorId',
				'setAuthorId',
				'getName',
				'setName',
				'getLastModifiedDatetime',
				'setLastModifiedDatetime',
				'getCreationDatetime',
				'setCreationDatetime',
				'getIsRetired',
				'setIsRetired',
				'loadBook_Collection',
				'getBook_Collection',
				'setBook_Collection',
				'addBook',
				'removeBook',
			),
			'Author_Collection' => array(),
			'Author_Collection_Generated' => array(),
			'Book' => array(
				'constructByKey',
				'constructBySql',
				'constructByFields',
				'getLoadSql'
			),
			'Book_Generated' => array(
				'notifyChildrenOfKeyChange',
				'getBookId',
				'setBookId',
				'getTitle',
				'setTitle',
				'getAuthorId',
				'setAuthorId',
				'getIntroduction',
				'setIntroduction',
				'getLastModifiedDatetime',
				'setLastModifiedDatetime',
				'getCreationDatetime',
				'setCreationDatetime',
				'getIsRetired',
				'setIsRetired',
				'loadAuthor_Object',
				'getAuthor_Object',
				'setAuthor_Object',
				'loadBook2library_Collection',
				'getBook2library_Collection',
				'setBook2library_Collection',
				'addBook2library',
				'removeBook2library',
			),
			'Book_Collection' => array(),
			'Book_Collection_Generated' => array(),
			'Book2library' => array(
				'constructByKey',
				'constructBySql',
				'constructByFields',
				'getLoadSql'
			),
			'Book2library_Generated' => array(
				'getBook2libraryId',
				'setBook2libraryId',
				'getBookId',
				'setBookId',
				'getLibraryId',
				'setLibraryId',
				'getLastModifiedDatetime',
				'setLastModifiedDatetime',
				'getCreationDatetime',
				'setCreationDatetime',
				'getIsRetired',
				'setIsRetired',
				'loadBook_Object',
				'getBook_Object',
				'setBook_Object',
				'loadLibrary_Object',
				'getLibrary_Object',
				'setLibrary_Object',
			),
			'Book2library_Collection' => array(),
			'Book2library_Collection_Generated' => array(),
			'Library' => array(
				'constructByKey',
				'constructBySql',
				'constructByFields',
				'getLoadSql'
			),
			'Library_Generated' => array(
				'notifyChildrenOfKeyChange',
				'getLibraryId',
				'setLibraryId',
				'getName',
				'setName',
				'getLastModifiedDatetime',
				'setLastModifiedDatetime',
				'getCreationDatetime',
				'setCreationDatetime',
				'getIsRetired',
				'setIsRetired',
				'loadBook2library_Collection',
				'getBook2library_Collection',
				'setBook2library_Collection',
				'addBook2library',
				'removeBook2library',
			),
			'Library_Collection' => array(),
			'Library_Collection_Generated' => array(),
		);
		$expectedClassNames = asort(array_keys($expectedClassNamesAndMethods));
		$generatedClassNames = asort(array_keys($classes));
		$this->assertEqual($expectedClassNames, $generatedClassNames, 'Incorrect class names generated.');
		
		// Include all the classes
		$this->includeGeneratedClasses($configName);
		
		// Test that we got the expected methods generated for each class.
		foreach ($expectedClassNamesAndMethods as $className => $expectedMethods)
		{
			foreach ($expectedMethods as $expectedMethod)
			{
				$this->assertTrue(method_exists($className, $expectedMethod), 'Method ' . $className . '::' . $expectedMethod . '() does not exist.');
			}
		}
		
		// TODO: go more in depth to make sure the generated queries JOIN correctly and alias correctly.
		
		
		// Remove the generated files
		$this->removeGeneratedClasses($configName);
		
		// Restore the database to it's state before running this test case
		$this->executeSqlFile($configPath . 'db_teardown.sql');
	}
	
	// TODO: Write another test method like the one above for a config that makes use of the class_name options (ignoring tables, adding prefixes, stripping table name prefixes, etc.)
	
	// TODO: Write another one for an FK schema (and don't pass the schema through the SchemaManipulator) to see if FK relationships are setup correctly without help from the SchemaManipulator.
	
	// TODO: We'll also have to figure out how to "uninclude" a class or make sure that each test uses different generated class names so we don't get "cannot redeclare class" errors
	
	
	/**
	 * A foreign key example...
	 *
	 * @author Anthony Bush
	 **/
	public function testConfig2()
	{
		$configName = 'test_config2';
		$configPath = dirname(__FILE__) . '/test_configs/' . $configName . '/';
		
		// Setup the database
		$this->executeSqlFile($configPath . 'db_setup.sql');
		
		// Which config to use?
		$schemaGeneratorConfigFile = $configPath . 'database_schema_generator.inc.php';
		$coughGeneratorConfigFile  = $configPath . 'cough_generator.inc.php';

		// Get the database config
		$schemaGeneratorConfig = DatabaseSchemaGeneratorConfig::constructFromFile($schemaGeneratorConfigFile);

		// Load the schema into memory
		$schemaGenerator = new DatabaseSchemaGenerator($schemaGeneratorConfig);
		$schema = $schemaGenerator->generateSchema();

		// Manipulate the schema (to add any missed relationships, e.g.)
		$manipulator = new SchemaManipulator($schemaGeneratorConfig);
		$manipulator->manipulateSchema($schema);

		// Get the cough generator config
		$coughGeneratorConfig = CoughGeneratorConfig::constructFromFile($coughGeneratorConfigFile);

		// Generate files into memory
		$coughGenerator = new CoughGenerator($coughGeneratorConfig);
		$classes = $coughGenerator->generateCoughClasses($schema);
		
		// Write files to disk
		$coughWriter = new CoughWriter($coughGeneratorConfig);
		$this->assertTrue($coughWriter->writeClasses($classes), 'Unable to write classes to disk.');
		
		// Start asserting
		
		// Test that the correct number of classes were generated
		$numClassesPerTable = 4;
		$numTables = 3;
		$expectedNumClasses = $numClassesPerTable * $numTables;
		$this->assertEqual(count($classes), $expectedNumClasses, 'Incorrect number of classes generated (' .  count($classes) . ' where generated, but ' . $expectedNumClasses . ' where expected).');
		
		// Test that we generated for the correct tables
		$tables = array('customer' => true, 'product' => true, 'product_order' => true);
		$tablesGeneratedFor = array();
		foreach ($classes as $class) {
			$tablesGeneratedFor[$class->getTableName()] = true;
		}
		$this->assertEqual(ksort($tables), ksort($tablesGeneratedFor), 'Did not generate for exactly the tables in question.');
		
		// Test that we got all the expected names for each class.
		$expectedClassNamesAndMethods = array(
			'Customer' => array(
				'constructByKey',
				'constructBySql',
				'constructByFields',
				'getLoadSql'
			),
			'Customer_Generated' => array(
				'notifyChildrenOfKeyChange',
				'getId',
				'setId',
				'loadProductOrder_Collection',
				'getProductOrder_Collection',
				'setProductOrder_Collection',
				'addProductOrder',
				'removeProductOrder',
			),
			'Customer_Collection' => array(),
			'Customer_Collection_Generated' => array(),
			'Product' => array(
				'constructByKey',
				'constructBySql',
				'constructByFields',
				'getLoadSql'
			),
			'Product_Generated' => array(
				'notifyChildrenOfKeyChange',
				'getCategory',
				'setCategory',
				'getId',
				'setId',
				'getPrice',
				'setPrice',
				'loadProductOrder_Collection',
				'getProductOrder_Collection',
				'setProductOrder_Collection',
				'addProductOrder',
				'removeProductOrder',
			),
			'Product_Collection' => array(),
			'Product_Collection_Generated' => array(),
			'ProductOrder' => array(
				'constructByKey',
				'constructBySql',
				'constructByFields',
				'getLoadSql'
			),
			'ProductOrder_Generated' => array(
				'getNo',
				'setNo',
				'getProductCategory',
				'setProductCategory',
				'getProductId',
				'setProductId',
				'getCustomerId',
				'setCustomerId',
				'loadProduct_Object',
				'getProduct_Object',
				'setProduct_Object',
				'loadCustomer_Object',
				'getCustomer_Object',
				'setCustomer_Object',
			),
			'ProductOrder_Collection' => array(),
			'ProductOrder_Collection_Generated' => array(),
		);
		$expectedClassNames = asort(array_keys($expectedClassNamesAndMethods));
		$generatedClassNames = asort(array_keys($classes));
		$this->assertEqual($expectedClassNames, $generatedClassNames, 'Incorrect class names generated.');
		
		// Include all the classes
		$this->includeGeneratedClasses($configName);
		
		// Test that we got the expected methods generated for each class.
		foreach ($expectedClassNamesAndMethods as $className => $expectedMethods)
		{
			foreach ($expectedMethods as $expectedMethod)
			{
				$this->assertTrue(method_exists($className, $expectedMethod), 'Method ' . $className . '::' . $expectedMethod . '() does not exist.');
			}
		}
		
		// TODO: go more in depth to make sure the generated queries JOIN correctly and alias correctly.
		
		
		// Remove the generated files
		$this->removeGeneratedClasses($configName);
		
		// Restore the database to it's state before running this test case
		$this->executeSqlFile($configPath . 'db_teardown.sql');
	}
	
	
	
}

?>