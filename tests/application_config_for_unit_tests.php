<?php

// Set error reporting
error_reporting(E_ALL);

// Set global path defines
define('PROJECT_PATH', dirname(__FILE__) . '/');
define('SHARED_PATH', dirname(dirname(__FILE__)) . '/shared/');
define('GENERATED_CLASS_PATH', PROJECT_PATH . 'generated/generated_classes/');
define('STARTER_CLASS_PATH', PROJECT_PATH . 'generated/starter_classes/');
define('CLASS_PATH', PROJECT_PATH . 'classes/');

// Set database config
$dbConfigs = array(
	'cough_test' => array(
		'db_name' => 'cough_test',
		'host' => 'localhost',
		'user' => 'nobody',
		'pass' => '',
	),
);
define('DB_HOST', $dbConfigs['cough_test']['host']); // Used directly by Cough stuff; it needs to be removed from the Cough stuff.
define('TEST_DBNAME', $dbConfigs['cough_test']['db_name']);

// Include some core files
include_once(CLASS_PATH . 'ClassIncluder.class.php');
include_once(SHARED_PATH . 'module_factory.inc.php');

// Configure the ModuleFactory
ModuleFactory::setIncludePaths(array(SHARED_PATH . 'modules/'));

// Load the core Cough classes
ModuleFactory::loadModule('cough');

// Load the Cough classes for this project
ClassIncluder::includeClassesInDir(GENERATED_CLASS_PATH);
ClassIncluder::includeClassesInDir(STARTER_CLASS_PATH);

// We should transation any DatabaseConnector stuff to Database stuff...
ModuleFactory::loadModule('matt_database');
DatabaseFactory::setDatabaseConfigs($dbConfigs);


?>