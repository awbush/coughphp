<?php

/**
 * This unit test's goal is to take three pieces of information (db_setup,
 * generator_config, and expected_output) and ensure that the given db_setup and
 * generator_config result in the expected_output.
 * 
 * This test class is *not* responsible for testing that generated classes work.
 * 
 * Order of operations for each test config:
 * 
 *     1. run the db_setup.sql
 *     2. run the generator to get the classes, and write them to disk.
 *     3. compare the generated results with the expected results
 *     4. delete the written classes (with option to leave behind classes that differed for better inspection, e.g w/ FileMerge)
 *     5. run the db_teardown.sql
 * 
 * Organization:
 * 
 * tests/ <- folder this file is in
 *    test_configs/
 *        test_config#/ (each folder matching test_configs* will be tested)
 *            db_setup/ (folder or symbolic link to folder containing:)
 *                db_setup.sql    <- SQL to setup all the tables in the database.
 *                db_teardown.sql <- SQL to drop everything that was setup in above file.
 *            generator_config/ (folder or symbolic link to folder containing:)
 *                cough_generator.inc.php           <- generator config that will be tested
 *                database_schema_generator.inc.php <- database config that will be tested
 *            expected_output/
 *                ... folders and files to be compared with generated output.
 *            output/ (all generator configs will be setup to write here, temporarily creating it if necessary)
 * 
 * @package default
 * @author Anthony Bush
 **/
class TestCoughGenerator extends UnitTestCase
{
	public function __construct()
	{
		// include cough generator, core cough, and the as_database DAL.
		require_once(dirname(dirname(__FILE__)) . '/load.inc.php');
		require_once(dirname(dirname(dirname(__FILE__))) . '/cough/load.inc.php');
		require_once(dirname(dirname(dirname(__FILE__))) . '/as_database/load.inc.php');
		
		// Setup DB config
		CoughDatabaseFactory::addConfig(array(
			'driver' => 'mysql',
			'host' => 'localhost',
			'db_name' => 'test_cough_object',
			'user' => 'cough_test',
			'pass' => 'cough_test',
			'port' => '3306',
			'aliases' => array('test_cough_object')
		));
	}
	
	public function executeSqlFile($sqlFile)
	{
		// We have to run this sql dump one query at a time
		$sqlCommands = explode(';', file_get_contents($sqlFile));
		
		// the last element is a blank string, so get rid of it
		array_pop($sqlCommands);

		$db = CoughDatabaseFactory::getDatabase('test_cough_object');
		foreach ($sqlCommands as $sql) {
			$db->query($sql);
		}
	}
	
	public function removeGeneratedClasses($classPath)
	{
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
	
	public function testConfigs()
	{
		$configPaths = glob(dirname(__FILE__) . '/test_configs/test_config*/');
		foreach ($configPaths as $configPath)
		{
			// 1. Setup DB
			$this->executeSqlFile($configPath . 'db_setup/db_setup.sql');
			
			// 2. Generate
			
			// Which config to use?
			$schemaGeneratorConfigFile = $configPath . 'generator_config/database_schema_generator.inc.php';
			$coughGeneratorConfigFile  = $configPath . 'generator_config/cough_generator.inc.php';

			// Get the database config
			$schemaGeneratorConfig = DatabaseSchemaGeneratorConfig::constructFromFile($schemaGeneratorConfigFile);

			// Load the schema into memory
			$schemaGenerator = new DatabaseSchemaGenerator($schemaGeneratorConfig);
			$schema = $schemaGenerator->generateSchema();

			// Manipulate the schema (to add any missed relationships, e.g.)
			$manipulator = new SchemaManipulator($schemaGeneratorConfig);
			$manipulator->manipulateSchema($schema);

			// Get the cough generator config
			$outputDir = $configPath . 'output/';
			include($coughGeneratorConfigFile);
			$coughGeneratorConfig = new CoughGeneratorConfig($config);

			// Generate files into memory
			$coughGenerator = new CoughGenerator($coughGeneratorConfig);
			$classes = $coughGenerator->generateCoughClasses($schema);

			// Write files to disk
			$coughWriter = new CoughWriter($coughGeneratorConfig);
			$this->assertTrue($coughWriter->writeClasses($classes), 'Unable to write classes to disk.');
			
			// 3. Perform comparison
			$diffCommand = 'diff -r ' . escapeshellarg($configPath . 'expected_output') . ' ' . escapeshellarg($configPath . 'output');
			$diffOutput = shell_exec($diffCommand);
			$message = "Generated output does not match; diff files using:\n"
			         . $diffCommand . "\n\n"
			         . "DIFF OUTPUT:\n"
			         . "==============================================\n"
			         . $diffOutput . "\n"
			         . "==============================================\n\n";
			$this->assertTrue(empty($diffOutput), $message);
			
			// 4. Clean up files
			if (empty($diffOutput))
			{
				$this->removeGeneratedClasses($configPath . 'output/');
			}
			
			// 5. Clean up DB
			$this->executeSqlFile($configPath . 'db_setup/db_teardown.sql');
		}
	}
	
	
	//////////////////////////////////////
	// Tests...
	//////////////////////////////////////
	
	// TODO: Write another test method like the one above for a config that makes use of the class_name options (ignoring tables, adding prefixes, stripping table name prefixes, etc.)
	
	// TODO: Write another one for an FK schema (and don't pass the schema through the SchemaManipulator) to see if FK relationships are setup correctly without help from the SchemaManipulator.
	
}

?>