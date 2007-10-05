<?php

define('GENERATOR_OUTPUT_PATH', dirname(__FILE__) . '/generated/');

// Setup autoloader for the generated classes
function __autoload($className) {
	if (file_exists(GENERATOR_OUTPUT_PATH . $className . '.class.php')) {
		include(GENERATOR_OUTPUT_PATH . $className . '.class.php');
	}
}

// Load up the Cough module
include_once('load.inc.php');

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

As_DatabaseFactory::setDatabaseConfigs($dbConfigs);




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
// if ($person->isInflated()) {
// 	echo 'Successfully pulled person id ' . $person->getPersonId() . ' (' . $person->getKeyId() . ') with name "' . $person->getName() . '"' . "\n";
// 	$person->setName('Anthony');
// 	$person->save();
// }
// print_r($person);





/////////////////////////////
// Test cough_test_fk tables
/////////////////////////////

$db = As_DatabaseFactory::getDatabase('cough_test_fk');
$db->startLoggingQueries();

try {
	$db->query('DELETE FROM product_order');
	$db->query('DELETE FROM product');
	$db->query('DELETE FROM customer');
	
	$product1 = new Product();
	$product1->setCategory(1);
	$product1->setId(1);
	$product1->setPrice(50.01);
	$product1->save();
	
	$product2 = new Product();
	$product2->setCategory(1);
	$product2->setId(2);
	$product2->setPrice(50.12);
	$product2->save();
	
	$customer = new Customer();
	$customer->setId(1);
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
	
	// $customer = new Customer(1);
	$orders = $customer->getProductOrder_Collection();
	foreach ($orders as $order) {
		echo 'Customer ' . $order->getCustomerId() . ' ordered category ' . $order->getProductCategory() . ', product ' . $order->getProductId() . "\n";
	}




	print_r($db->getQueryLog());
	die();
} catch (Exception $e) {
	echo 'Query Log:' . "\n";
	print_r($db->getQueryLog());
	throw $e;
}


?>