<?php

$config = array(
	// REQUIRED CONFIG
	
	'database_settings' => array(
		'include_databases_matching_regex' => '/^(test_cough_object)$/',
	),
	
	// All databases will be scanned unless specified in the 'databases' parameter in the OPTIONAL CONFIG SECTION.
	'dsn' => array(
		'host' => 'localhost',
		'user' => 'cough_test',
		'pass' => 'cough_test',
		'port' => 3306,
		'driver' => 'mysql'
	),
	
	// OPTIONAL ADDITIONAL CONFIG
	
	'databases' => array(
		'test_cough_object' => array(),
	),
	
);

?>