<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>Cough Test</title>
</head>
<body>
	<h1>Cough Test</h1>
	<?php

	include_once('application.inc.php');
	
	
	/* Test FOUND_ROWS()
	$db = DatabaseFactory::getDatabase('content');
	$result = $db->doQuery('SELECT SQL_CALC_FOUND_ROWS * FROM product LIMIT 0, 100');
	echo 'Num rows: ' . $result->numRows();
	
	$result = $db->doQuery('SELECT FOUND_ROWS()');
	while ($row = $result->getRow()) {
		echo '<pre>';
		print_r($row);
		echo '</pre>';
	}
	die();
	//*/
	
	
	
	// Truncate all the test tables
	/* No permissions...
	$truncateSql = '
		TRUNCATE customer;
		TRUNCATE school;
		TRUNCATE school2customer;
		TRUNCATE political_party;
	';
	$db = DatabaseFactory::getDatabase(TEST_DBNAME);
	$db->doQuery($truncateSql);
	*/
	
	/* Already ran this
	$party = new PoliticalParty();
	$party->setPoliticalPartyName('Democratic');
	$party->save();
	
	$party = new PoliticalParty();
	$party->setPoliticalPartyName('Republican');
	$party->save();
	
	$party = new PoliticalParty();
	$party->setPoliticalPartyName('Libertarian');
	$party->save();
	
	$party = new PoliticalParty();
	$party->setPoliticalPartyName('Green');
	$party->save();

	$customer = new Customer();
	$customer->setCustomerName('Anthony');
	$customer->setPoliticalPartyID(2);
	$customer->save();
	
	$school = new School();
	$school->setSchoolName('Jedi Master Institution');
	$school->save();
	//*/
	
	/* Save Lewis!
	$customer = new Customer();
	$customer->setName('Lewis');
	$customer->setPoliticalPartyID(2);
	$customer->save();
	//*/
	
	/* Can change attributes of customer
	$customer = new Customer(1);
	$customer->setPoliticalPartyID(2);
	$customer->save();
	
	// Test CoughObject::saveOneToManyCollection() by removing the customer from the political party using a political party object.
	
	$party = new PoliticalParty(2);
	$party->checkCustomer_Collection();
	// Remove Anthony from the Republican party
	$party->removeCustomer($customer->getKeyID());
	$party->save();
	//*/
	
	
	/* View all customers in a party (one-to-many relationship)
	$party = new PoliticalParty(2);
	$party->checkCustomer_Collection();
	echo('The ' . $party->getPoliticalPartyName() . ' political party has customers:<br />');
	foreach ($party->getCustomer_Collection() as $customerID => $customer) {
		echo($customerID . ': ' . $customer->getCustomerName() . "<br />\n");
	}
	//*/
	
	
	/* Check if a newly instantiated object actually existed in the database.
	$party = new PoliticalParty(2);
	if ($party->didCheckReturnResult()) {
		echo('Returned Result' . "<br />\n");
	} else {
		echo('Did NOT Return Result' . "<br />\n");
	}
	//*/
	
	/* Test new accessors added 2007-02-01
	$party = new PoliticalParty(2);
	$party->checkCustomer_Collection();
	$customers = $party->getCustomer_Collection();
	echo $customers->getFirst()->getName() . "<br />\n";
	echo $customers->getLast()->getName() . "<br />\n";
	echo $customers->getRandom()->getName() . "<br />\n";
	echo $customers->getPosition(1)->getName() . "<br />\n";
	//*/
	
	/* Test Clone
	$party = new PoliticalParty(2);
	$party->checkCustomer_Collection();
	$party->setName('SDFLKJ');
	echo '<h3>Original</h3>';
	echo '<pre>';
	print_r($party);
	echo '</pre>';
	
	echo '<h3>Clone</h3>';
	$party2 = clone $party;
	echo '<pre>';
	print_r($party2);
	echo '</pre>';
	//*/
	
	/* Test getGetter
	ModuleFactory::loadModule('conversion');
	$party = new PoliticalParty(2);
	echo $party->getGetter('political_party_id');
	echo $party->getGetter('political_party_name');
	//*/
	
	
	// foreach ($customers as $customerID => $customer) {
	// 	$party->removeCustomer($customerID);
	// }
	// $party->save();
	
	// echo '<pre>';
	// print_r($customers->getFirst());
	// echo '</pre>';
	
	// echo '<pre>';
	// print_r($customers->getLast());
	// echo '</pre>';
	// echo '<pre>';
	// print_r($customers->getRandom());
	// echo '</pre>';
	
	
	
	/* Test CheckOnceAndGetCollection
	$party = new PoliticalParty(2);
	foreach ($party->coagCustomer_Collection() as $customer) {
		echo '<pre>';
		print_r($customer);
		echo '</pre>';
	}
	//*/

	/*
	$customer = new Customer(1);
	$schools = $customer->coagSchool_Collection();
	$schools[1]->setJoinField('is_retired', 1);
	$customer->save();
	return;
	//*/
	
	/*
	$customer = new Customer(1);
	$customer->checkSchool_Collection();
	$customer->removeSchool(1);
	$customer->save();
	return;
	//*/
	
	
	// Test join table setting as an add
	$school = new School(1);
	$school2 = new School();
	$school2->setName('School Foo');
	
	$customer = new Customer(1);
	
	// Should be NULL
	//var_dump($customer->getJoinTable('school2customer'));
	
	$joinFields = array(
		  'school2customer_type_id' => 1
		, 'relationship_start_date' => date('Y-m-d H:i:s')
		, 'relationship_end_date' => date('Y-m-d H:i:s')
	//	, 'school_id' => $school->getKeyID()
	//	, 'customer_id' => $customer->getKeyID()
	//	, 'is_retired' => 0
	);
	$customer->addSchool($school, $joinFields);
	//$customer->addSchool($school);
	//$customer->addSchool($school2, $joinFields);
	echo 'Collection Size: ' . count($customer->getSchool_Collection()) . "<br />\n";
	$customer->save();
	
	// Test join table setting as an update
	$db = DatabaseFactory::getDatabase('cough_test');
	$result = $db->doQuery('SELECT * FROM school2customer');
	while ($row = $db->getRow()) {
		echo '<pre>';
		print_r($row);
		echo '</pre>';

		
	}
	
	// Delete everything from join table so we can test over and over.
	$sql = 'TRUNCATE school2customer';
	//$db->doQuery($sql);
	
	
	
	
	?>
</body>
</html>
