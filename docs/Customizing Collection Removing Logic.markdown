Customizing Collection Removing Logic
=====================================

If special actions need to take place when removing an item from an object's collection, simply override the `removeItemName()` method or provide a new, better named method that calls remove for you and performs extra logic afterwards.

For example, if we have an `Order` that contains an `OrderLine` collection, we might want to keep the order line in the database for historical reasons; We could toggle an `is_deleted` flag rather than deleting the record from the database.

	<?php
	class Order /* ... */ {
		// In this delete example, we most likely override the
		// OrderLine_Collection load SQL so that it does not pull deleted order
		// lines.  Thus, we call remove on the collection and then set the
		// is_deleted column.
		public function deleteOrderLine($objectOrId) {
			$orderLine = $this->getOrderLine_Collection()->remove($objectOrId);
			$orderLine->setIsDeleted(true);
		}
		
		// In the cancel example, we may want to keep the cancelled order line
		// in the collection so we can display on pages with a cancellation
		// status, so we do not call remove() on the collection and instead
		// just get the object.
		public function cancelOrderLine($objectOrId, $cancelReasonId) {
			if (is_object($objectOrId)) {
				$orderLine = $objectOrId;
			} else {
				$orderLine = $this->getOrderLine_Collection()->get($objectOrId);
			}
			$orderLine->setIsCancelled(true);
			$orderLine->setCancelReasonId($cancelReasonId);
		}
	}
	?>

Thus, the following code would remove the order line from the collection and delete the order line.

	$order->deleteOrderLine(3); // remove order line with order_line_id = 3
	$order->save(); // order_line record will not be updated until save is called on parent.

The cancelling works the same way:

	$order->cancelOrderLine(3); // cancel order line with order_line_id = 3
	$order->save(); // order_line record will not be updated until save is called on parent.
