<?php

try {
	// Include the Cough generation classes
	include_once(dirname(__FILE__) . '/config/application.inc.php');
	
	// Which config to use?
	$configName = 'cough_test';

	// Database Schema Generator example

	// Get the database config
	include(CONFIG_PATH . $configName . '/database_schema_generator.inc.php');
	$schemaGeneratorConfig = new DatabaseSchemaGeneratorConfig($config);

	// Load the schema into memory
	$schemaGenerator = new DatabaseSchemaGenerator($schemaGeneratorConfig);
	$schemaGenerator->enableVerbose();
	$schema = $schemaGenerator->generateSchema();
	
	// TODO: ? Allow external additions to the schema... e.g. loop through it with any custom naming standards and add any FK data based on those standards, e.g. using the cough naming conventions (or configuration?).
	// $manipulator = new SchemaManipulator();
	// $manipulator->addFksFromJointables($schema, '/.*2.*/');

	// Get the cough generator config
	include(CONFIG_PATH . $configName . '/cough_generator.inc.php');
	$coughGeneratorConfig = new CoughGeneratorConfig($config);

	// Generate files into memory
	$coughGenerator = new CoughGenerator($coughGeneratorConfig);
	$classes = $coughGenerator->generateCoughClasses($schema);
	
	echo number_format(memory_get_usage()) . " used\n";
	echo number_format(memory_get_usage(true)) . " allocated\n";
	
	print_r($classes);
	
} catch (Exception $e) {
	echo $e->getMessage() . "\n";
	
	if (DEV) {
		echo 'Trace:' . "\n";
		print_r($e->getTrace());
	}
}





?>