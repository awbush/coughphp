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




// Testing cough_test database tables
// // Start using the classes
// $person = Person::constructByKey(1);
// if ($person) {
// 	echo 'Successfully pulled person id ' . $person->getPersonId() . ' (' . $person->getKeyId() . ') with name "' . $person->getName() . '"' . "\n";
// 	$person->setName('Anthony TEST');
// 	$person->save();
// }
// print_r($person);
// 
// // We could support the old way, if we wanted... (check the inflate method in new cough, there is a line with `$this->load()` comment it out to disable this feature and enable easy one-query updates...)
// $person = new Person(1);
// if ($person->isLoaded()) {
// 	echo 'Successfully pulled person id ' . $person->getPersonId() . ' (' . $person->getKeyId() . ') with name "' . $person->getName() . '"' . "\n";
// 	$person->setName('Anthony');
// 	$person->save();
// }
// print_r($person);





/////////////////////////////
// Test cough_test_fk tables
/////////////////////////////

$db = DatabaseFactory::getDatabase('cough_test_fk');
$db->startLoggingQueries();

$db->query('DELETE FROM product_order');
$db->query('DELETE FROM product');
$db->query('DELETE FROM customer');

$product1 = new Product();
// $product->setCategory('Computers');
// $product->setId(1);
$product1->setPreknownKeyId(array(
	'category' => 1,
	'id' => 1
));
$product1->setPrice(50.01);
$product1->save();

$product2 = new Product();
$product2->setPreknownKeyId(array(
	'category' => 1,
	'id' => 2
));
$product2->setPrice(50.12);
$product2->save();

$customer = new Customer();
$customer->setPreknownKeyId(array('id' => 1));
$customer->setName('Anthony');
$customer->save();

$order = new ProductOrder();
$order->setProductCategory($product1->getCategory());
$order->setProductId($product1->getId());
$order->setCustomerId($customer->getId());
$order->save();

$order = new ProductOrder();
$order->setProductCategory($product2->getCategory());
$order->setProductId($product2->getId());
$order->setCustomerId($customer->getId());
$order->save();


$orders = $customer->getProductOrder_Collection();
foreach ($orders as $order) {
	echo 'Customer ' . $order->getCustomerId() . ' ordered category ' . $order->getProductCategory() . ', product ' . $order->getProductId() . "\n";
}




print_r($db->getQueryLog());
die();

?>