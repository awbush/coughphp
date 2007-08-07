
Confirmed
---------

* No factory methods
* Replace all `check` methods with `load` methods.
* Centralize an object's generic SQL without a WHERE clause so it can be reused by itself, related object that have a one-to-one relationship, and collections.
* Simplify the definitions for objects and collections so that the work is done by load methods which can easily be overridden.

* Getters, Setters, Loaders, and other method names are generated from the entities in the schema, not the class names.

	For example, you could have a `product` table inside a `content` database, and in your Cough Generator config specify that all classes for the `content` database be prefixed with `con_`. This will only effect the class name, and not the methods:
	
		class con_Product /* ... */ {
			// ...
		}
	
	If another class is related to this, e.g. via `product_id` column, their methods will look like:
	
		getProduct_Object()
		setProduct_Object($product)
		loadProduct_Object()

	In other words, the class prefix is only available to help you avoid name-spacing issues, not to clutter all your method names.

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
