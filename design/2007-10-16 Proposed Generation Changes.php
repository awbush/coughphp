<?php

class Order_Generated {
	// proposed new "add" method:
	public function addOrderLine($objectOrId) {
		if (is_object($objectOrId)) {
			$object = $objectOrId;
		} else {
			$object = OrderLine::retrievByPk($objectOrId);
		}
		$object->setObjectFooId($this->getOrderId());
		$this->getOrderLine_Collection()->add($object);
		return $object; // also a new concept: return the added item in case user wants to perform more operations on it?
	}
	
	// proposed new "setKeyId" method:
	public function setKeyId($keyId) {
		parent::setKeyId($keyId);
		$this->notifyChildrenOfKeyChange($keyId);
	}
	
	// proposed new method that setKeyId will call:
	public function notifyChildrenOfKeyChange($keyId) {
		$this->getOrderLine_Collection()->setOrderId($keyId);
		$this->getOrderNotes_Collection()->setOrderId($keyId);
		// etc. for all collections that have an FK to this object's PK
	}
}

class OrderLine_Collection_Generated {
	// proposed new NOTIFICATION method that parent objects can use.
	public function setOrderId($orderId) {
		// generator by default shall pass on the nofication as "sets" on the child objects (to enable "single save" feature
		foreach ($this as $element) {
			$element->setOrderId($orderId);
		}
	}
	// One note: we should keep the method on the collection the same as the children or the parent object? (e.g. what to name it when it's order.order_id to order_line.foo_id; do we name it setOrderId or setFooId on the collection? I believe in the latter, because what if it is foo_id for a reason, e.g. if you went insane and also added an order_id column which is not the proper FK even though it's name suggests it.)
	
}


?>