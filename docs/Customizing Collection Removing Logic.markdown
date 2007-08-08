Customizing Collection Removing Logic
=====================================

If special actions need to take place when removing an item from an object's collection, simply override the `removeItemName()` method or provide a new, better named method that calls remove for you and performs extra logic afterwards.

For example, if we have an `Order` that contains an `OrderLine` collection, we might want to keep the order line in the database for historical reasons; We could toggle an `is_canclled` flag rather than deleting the record from the database.

	<?php
	class Order /* ... */ {
		// ...
		public function removeOrderLine($objectOrId) {
			$orderLine = parent::removeOrderLine($objectOrId);
			$orderLine->setIsCancelled(true);
		}
	}
	?>

Thus, the following code would remove the order line from the collection and cancel the order line.

	$order->removeOrderLine(3); // remove order line with order_line_id = 3
	$order->save(); // order_line record will not be updated until save is called on parent.

It may be good practice to provide a separate function whose name makes more sense; In the example above, `cancelOrderLine` would be a better fit.
