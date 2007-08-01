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

	// Get the cough generator config
	include(CONFIG_PATH . $configName . '/cough_generator.inc.php');
	$coughGeneratorConfig = new CoughGeneratorConfig($config);

	// Load the schema into memory
	$schemaGenerator = new DatabaseSchemaGenerator($schemaGeneratorConfig);
	$schemaGenerator->enableVerbose();
	$schema = $schemaGenerator->generateSchema();
	
} catch (Exception $e) {
	echo $e->getMessage() . "\n";
	
	if (DEV) {
		echo 'Trace:' . "\n";
		print_r($e->getTrace());
	}
}





?>