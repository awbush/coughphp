<?php


// Config...
define('SHARED_PATH',    '/Users/awbush/Projects/Shared/');
define('AS_SHARED_PATH', '/Users/awbush/Projects/Academic Superstore/html/shared/');


// Database configuration for all databases we'll use; currently only one.
$dbConfig = array(
	'test_coughphp' => array(
		'db_name' => 'test_coughphp',
		'host' => 'localhost',
		'user' => 'nobody',
		'pass' => ''
	),
);


// Derived
define('CLASSES_PATH', dirname(__FILE__) . '/classes/');


// Setup autoloader
function __autoload($className) {
	$fileName = CLASS_PATH . $className . '.class.php';
	if (file_exists($fileName) && is_readable($fileName)) {
		include($fileName);
	}
}


// Turn on error reporting and make sure the errors display to the screen (FOR TESTING ONLY).
error_reporting(E_ALL);
ini_set('display_errors', true);


// Load & configure DatabaseFactory
include_once(AS_SHARED_PATH . 'modules/matt_database/load.inc.php');
DatabaseFactory::addDatabaseConfig('test_coughphp', $dbConfig['test_coughphp']);


// Load & configure the CoughPHP module
include_once(dirname(dirname(__FILE__)) . '/load.inc.php');
// CoughLoader::addModelPath(APP_PATH . 'models/cough/');



?>