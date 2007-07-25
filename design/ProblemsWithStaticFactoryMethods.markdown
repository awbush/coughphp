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





Another Solution (2007-07-25)
-----------------------------

We can leave the existing check/load methods in place and have the static methods call them... Note that this is somewhat inefficient because in the default case of constructByKey we construct two objects in memory, discarding one. Collections will not be effected by this, but simple stuff will:

	$ticket = Ticket::construct($id);

construct
constructByKey
constructByFields

	public static function construct($idOrFields) {
		if (is_array($idOrFields)) {
			return self::constructByFields($idOrFields);
		} else {
			return self::constructByKey($idOrFields);
		}
	}
	
	public static function constructByKey($idOrHash) {
		// We need to run SQL to get it... Only other way is to do this:
		$obj = new ConcreteClassName($idOrHash);
		if (is_array($idOrHash)) {
			$obj->load();
		} else {
			// don't need to load if we keep the object autoloading when a non-array value is passed.
		}
		// now discard the object we just created and pass controll to constructByFields:
		return self::constructByFields($obj->getFields());
	}
	
	public static function constructByFields($hash) {
		// DEFAULT:
		return new ConcreteClassName($hash); // note that we are saying the generator must provide this method because it won't work otherwise, i.e. class name is not available from within a static method call.
		
		// POSSIBLE OVERRIDEN BEHAVIOR.
		switch ($hash['type']) {
			case 'type_one':
				return new TypeOne($hash);
			break;
			case 'type_two':
				return new TypeTwo($hash);
			break;
			default:
				return new ConcreteClassName($hash);
			break;
		}
	}
	
	
