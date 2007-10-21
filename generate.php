<?php

try {
	// Include the Cough generation classes
	include_once(dirname(__FILE__) . '/config/application.inc.php');
	
	// Which config to use?
	$configName = 'cough_test';
	$schemaGeneratorConfigFile = CONFIG_PATH . $configName . '/database_schema_generator.inc.php';
	$coughGeneratorConfigFile  = CONFIG_PATH . $configName . '/cough_generator.inc.php';

	// Get the database config
	$schemaGeneratorConfig = DatabaseSchemaGeneratorConfig::constructFromFile($schemaGeneratorConfigFile);

	// Load the schema into memory
	$schemaGenerator = new DatabaseSchemaGenerator($schemaGeneratorConfig);
	$schemaGenerator->enableVerbose();
	$schema = $schemaGenerator->generateSchema();
	
	// Dump some verbose messages.
	echo "\n";
	$schema->outputRelationshipCounts();
	
	echo "\n" . 'About to run the SchemaManipulator and re-output the relationships.' . "\n";
	
	// Manipulate the schema (to add any missed relationships, e.g.)
	$manipulator = new SchemaManipulator($schemaGeneratorConfig);
	$manipulator->enableVerbose();
	$manipulator->manipulateSchema($schema);
	
	// Dump some verbose messages again to see if the manipulator added anything.
	echo "\n";
	$schema->outputRelationshipCounts();
	
	// Get the cough generator config
	$coughGeneratorConfig = CoughGeneratorConfig::constructFromFile($coughGeneratorConfigFile);

	// Generate files into memory
	$coughGenerator = new CoughGenerator($coughGeneratorConfig);
	$classes = $coughGenerator->generateCoughClasses($schema);
	
	// Write files to disk
	echo "\n";
	$coughWriter = new CoughWriter($coughGeneratorConfig);
	if (!$coughWriter->writeClasses($classes)) {
		echo 'Trouble writing classes:' . "\n";
		echo '------------------------' . "\n";
		foreach ($coughWriter->getErrorMessages() as $message) {
			echo $message . "\n";
		}
		echo "\n";
	} else {
		echo 'Success writing ' . count($classes) . ' classes!' . "\n";
	}
	
	echo "\n" . 'PHP memory usage:' . "\n";
	echo number_format(memory_get_usage()) . " used\n";
	echo number_format(memory_get_usage(true)) . " allocated\n";
	
	// print_r($classes);
	
} catch (Exception $e) {
	echo $e->getMessage() . "\n";
	
	if (DEV) {
		echo 'Trace:' . "\n";
		print_r($e->getTrace());
	}
}

?>