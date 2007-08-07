
Confirmed
---------



Unconfirmed
-----------

Factory methods?

Add a parameter to loadObjectName_Object() methods to allow loading via some pre-pulled criteria?

	<?php
	public function loadManufacturer_Object($hashOrObject = null) {
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
	?>
