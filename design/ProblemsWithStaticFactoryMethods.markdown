<?php

abstract class CoughObject {
	protected static $dbName = null;
	
	public function printDbName() {
		echo 'Database Name: ' . self::$dbName . "\n";
	}
}

class TestObject extends CoughObject {
	protected static $dbName = 'test_db_name';
	
}

$testObj = new TestObject();
$testObj->printDbName();
// Because dbName is static, we get the wrong value. If we copy and paste the printDbName function into the TestObject class, then we get the right value, but that doesn't help us.

// The only thing I can think of so far is to either (1) don't put the `protected static $dbName = null;` into the abstract class or (2) find another way using non-static variables to make the factory retrieveByPk methods work.
// Note that (1) works but has the same problem on the next class that extends it; For example If we have BundleProduct extends Product then the Product static variables are the ones we see.

?>

These problems can be fixed if we want to make the generation a required part of building Cough classes. We can give the user the access to a schema file (e.g. XML schema like Propel) where they can perform customizations...

The idea is that in the above example  we can remove the protected static $dbName = null; from CoughObject and it will work. Extending TestObject and trying to change the dbName will be considered invalid, and bad practice. To have to simpilar objects on different databases the desired thing would be to make TestObject abstract, remove the protected static $dbName = 'test_db_name'; and write two new classes, TestObjectA and TestObjectB that each set the dbName to what they each need.

