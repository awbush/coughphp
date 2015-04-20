---
title: Dealing With Non-Retired Items - CoughPHP
---

Dealing With Non-Retired Items
==============================

If you use a schema that involves "retiring" items instead of deleting them, then you may wonder the best ways to deal with pulling only non-retired data.

Let's examine the following schema:

	address
	-------
	address_id
	customer_id
	address1
	address2
	city
	state
	zip
	is_retired
	
	customer
	--------
	customer_id
	name
	is_retired
	
	order
	-----
	order_id
	customer_id
	billing_address_id
	shipping_address_id
	...

The idea is that when a customer removes an address we only "retire" it in the database rather than delete it.  This is useful because the address may already have been used on an old order; we don't want to delete the order because it is still valid and we don't want to delete the address because we'd lose the data of where we shipped the order.

At the same we don't want to force the customer to pick from a bunch of outdated addresses so we mark it "retired" so we know not to show it to them anymore.

There are two ways to code for this:

1. **In the application:** write code to look at the value of `is_retired` whenever looping through collections or retrieving data.
2. **In the model:** override the default load methods to look at the `is_retired` value.

It may be useful to use both, depending on the goal.

Example: Handling retired data in collections
---------------------------------------------

When displaying the customer's addresses, the code might look like:

	<?php
	$customer = Customer::constructByKey($customerId);
	foreach ($customer->getAddress_Collection() as $address) {
		echo $address->getAddress1() . "\n";
		echo $address->getAddress2() . "\n";
		echo $address->getCity() . "\n";
		echo $address->getState() . "\n";
		echo $address->getZip() . "\n\n";
	}
	?>

To only display the non-retired ones you could simply check the `is_retired` value:

	<?php
	$customer = Customer::constructByKey($customerId);
	foreach ($customer->getAddress_Collection() as $address) {
		if ($address->getIsRetired()) {
			continue;
		}
		echo $address->getAddress1() . "\n";
		echo $address->getAddress2() . "\n";
		echo $address->getCity() . "\n";
		echo $address->getState() . "\n";
		echo $address->getZip() . "\n\n";
	}
	?>

Now, the first iteration would work fine if we changed how the model retrieves addresses for a customer.  The easiest thing to do is to copy the load method from the generated class, paste it into the concrete class, and then change it.  In this case, that would be `loadAddress_Collection()` and we only have to add one line to the WHERE clause: `AND address.is_retired = 0`:

	<?php
	class Customer
	{
		public function loadAddress_Collection()
		{
			// Always create the collection
			$collection = new Address_Collection();
			$this->setAddress_Collection($collection);
			
			// But only populate it if we have key ID
			if ($this->hasKeyId())
			{
				$db = Address::getDb();
				$sql = '
					SELECT
						`address`.*
					FROM
						`address`
					WHERE
						`address`.`customer_id` = ' . $db->quote($this->getCustomerId()) . '
						AND `address`.`is_retired` = 0
				';
				
				// Construct and populate the collection
				$collection->loadBySql($sql);
				foreach ($collection as $element) {
					$element->setCustomer_Object($this);
				}
			}
		}
	}
	?>

Easy.

Example: Handling retired data in objects
-----------------------------------------

What if the customer hacks the FORM data when picking an address so that their retired address is chosen?  The controller code for pulling the address may have looked like:

	<?php
	$address = Address::constructByKey($_POST['address_id']);
	if (!is_object($address)) {
		$errors[] = 'Please select a valid address';
	}
	?>

But, what if the ID for the retired address is provided?

We can check for it after getting the object:

	<?php
	$address = Address::constructByKey($_POST['address_id']);
	if (!is_object($address) || $address->getIsRetired()) {
		$errors[] = 'Please select a valid address';
	}
	?>

Or, we can add the criteria to the `constructByKey` call:

	<?php
	$address = Address::constructByKey(array(
		'address_id' => $_POST['address_id'],
		'is_retired' => 0
	));
	if (!is_object($address)) {
		$errors[] = 'Please select a valid address';
	}
	?>

Or, we can change the default behavior of pulling addresses by overloading the static method `getLoadSql()` on the `Address` class.  Note that if we do this we must use the `As_SelectQuery` class rather than a plain string.

	<?php
	class Address
	{
		public static function getLoadSql()
		{
			$sql = new As_SelectQuery(self::getDb());
			$sql->setSelect('address.*');
			$sql->setFrom('address');
			$sql->setWhere('address.is_retired = 0');
			return $sql;
		}
	}
	?>

**NOTE:** In this example, overriding `getLoadSql` is a bad idea as we may want the ability to construct a retired address object in order to remove its retired status or to display the address on any orders it may have already been used on.  In other words, adding the retired check to base SQL is like treating the retired status the same as having run a DELETE query; in this example, the retired status is *not* the same as we would still want `$order->getShippingAddress_Object()` to return the retired address data.

**REALITY CHECK:** In the real world an extra check should be made to ensure that the address belongs to the customer.  Assuming we have the customer's ID in the session, we could use `constructByKey` again but this time provide both the address ID and the customer ID:

	<?php
	$address = Address::constructByKey(array(
		'address_id' => $_POST['address_id'],
		'customer_id' => $_SESSION['customer_id'],
		'is_retired' => 0
	));
	if (!is_object($address)) {
		$errors[] = 'Please select a valid address';
	}
	?>

Recap
-----

The best practices of dealing with retired data might be:

* Do not override the `getLoadSql` method unless you want to treat "retired" statuses the same as having run a DELETE query.
* When looping through a collections, override the `load*_Collection()` method to ensure only non-retired data is returned.
	* If needing access to retired data, add `get/load/set*_Collection()` methods for the retired data; e.g. in the `Customer` class we might add `getRetiredAddress_Collection()`, `loadRetiredAddress_Collection()`, and `setRetiredAddress_Collection()`.
* When pulling a single object, check for the retired status in code.
