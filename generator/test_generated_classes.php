<?php

define('COUGH_MODULE_PATH', dirname(dirname(__FILE__)) . '/');
define('GENERATOR_OUTPUT_PATH', dirname(__FILE__) . '/generated/');

// Setup autoloader for the generated classes
function __autoload($className) {
	if (file_exists(GENERATOR_OUTPUT_PATH . $className . '.class.php')) {
		include(GENERATOR_OUTPUT_PATH . $className . '.class.php');
	}
}

// Load up the Cough module
include_once(COUGH_MODULE_PATH . 'load.inc.php');

// Load up the Database module (a Cough dependency)
include_once(dirname(dirname(dirname(__FILE__))) . '/database/load.inc.php');

// Specify Database Configuration... (server, user, pass)
$dbConfigs = array(
	'cough_test' => array(
		'db_name' => 'cough_test',
		'host' => 'localhost',
		'user' => 'nobody',
		'pass' => '',
		'port' => 3306,
		'driver' => 'mysql'
	)
);

DatabaseFactory::setDatabaseConfigs($dbConfigs);





// Start using the classes
$person = Person::constructByKey(1);
if ($person) {
	echo 'Successfully pulled person id ' . $person->getPersonId() . ' (' . $person->getKeyId() . ') with name "' . $person->getName() . '"' . "\n";
}
print_r($person);

// We could support the old way, if we wanted... (check the inflate method in new cough, there is a line with `$this->load()` comment it out to disable this feature and enable easy one-query updates...)
$person = new Person(1);
if ($person->isLoaded()) {
	echo 'Successfully pulled person id ' . $person->getPersonId() . ' (' . $person->getKeyId() . ') with name "' . $person->getName() . '"' . "\n";
}
print_r($person);


?>