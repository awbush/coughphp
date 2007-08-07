
Confirmed
---------

* No factory methods
* Replace all `check` methods with `load` methods.
* Centralize an object's generic SQL without a WHERE clause so it can be reused by itself, related object that have a one-to-one relationship, and collections.
* Simplify the definitions for objects and collections so that the work is done by load methods which can easily be overridden.


Generation
----------

Load methods for one-to-one relationships will take _no_ parameters and will _always set the value to an object, even if it is not in the database or the key is not set yet_.

<?php
class Product {
	public function loadManufacturer_Object() {
		$fields = array(
			'asdlfkj'
		);
		
		if (is_null($hashOrObject)) {
			// Do db lookup to get hash.
			$sql = Manufacturer::getLoadSqlWithoutWhere();
			$sql . = ' WHERE ' . $this->getDb()->generateWhere($this->getPk())
		}
		else if (is_array($hashOrObject)) {
			// We got the data
		}
		else if (is_object($hashOrObject)) {
			// We got the object, just set it:
			$this->setObject('manufacturer', $hashOrObject);
		}
	}
}
?>
