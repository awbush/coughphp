
<?php

// WHAT WE ARE GOING WITH (see below for full DISCUSSION ideas)

// TODO: add this getter.
$element = $this->getOrderLine_Collection()->get($object->getPk())

// TODO: make sure getKeyId always returns a "string" / integer
$hash = $object->getKeyId();

// TODO: standarize CoughCollection API to more closely match CoughObject (e.g. loadBySql, etc.)

class Order_Generated {
	// proposed new "add" method:
	public function addOrderLine(OrderLine $object) {
		$object->setObjectId($this->getOrderId());
		$object->setOrder_Object($this);
		$this->getOrderLine_Collection()->add($object);
		return $object; // also a new concept: return the added item in case user wants to perform more operations on it?
	}
	
	public function removeOrderLine($objectOrId) {
		$object = $this->getOrderLine_Collection()->remove($objectOrId);
		$object->setOrderId(null);
		$object->setOrder_Object(null);
		return $object;
	}
	
	// proposed new method that setKeyId will call:
	public function notifyChildrenOfKeyChange(array $key) {
		$orderLineFields = array(
			'order_id' => $key['order_id']
		);
		$this->getOrderLine_Collection()->callMethodOnChildren('setFields', array($orderLineFields));
		
		$orderNoteFields = array(
			'order_id' => $key['order_id']
		);
		$this->getOrderNotes_Collection()->callMethodOnChildren('setFields', array($orderNoteFields));
		// etc. for all collections that have an FK to this object's PK
		
		// BTW, this implies that all the loads for the collections should not run queries in the case of key ID changing from NULL -> non-NULL. instead, empty collections should be constructed. Question is, do we have to set them as isPopulated?
	}
}

class OrderLine_Collection_Generated {
	// No new generated methods here. We will use the new generic method CoughCollection::callMethodOnChildren($method, $params);
}




// DISCUSSION
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

// Mode -> Select Mode -> PHP-HTML :) dude then how come this is a .sh file :( it's not anymore... that was back when it was untitled. (You can save a local copy to any file name you WANT) cool....... alright dude check out the example

$order->loadOrderLine_Collection();
$poLine->loadOrderLine_Object(); // Instance Poool says bizonk! here is the same object
//??? is this a problem? WHO is the parent???? poLine or Orderrr
// both are "parents" sort of? I'm saying it doesn't matter... if the parent tells the order line what to do then it doesn't matter. Order says "OrderLine set your OrderId b/c I have changed" Po line does nothing

// POST NOTE: Actually the Po line is a child to the order line and the question is "does the order line tell the PO line when it's ID changes?" and the answer is yes. You add a Po line to the order line collection (if you want the ID to be set).

// Maybe we just need generic collection method:
$collection->setField($fieldName, $fieldValue); // which calls the same method on all children. then, in the case of setFieldOnChildren('order_foo_id', $orderId); from the Order object... this means the generator has less work, there is less overrhead in PHP's memory

// ok sounds good... this was the motivation for removing getCollector in the first palce right
// yes, motiviation on removing it.
// ok all our problems make sense to me now. EXELECENT! I am fine with the SET you are right it is only solving an edge case and it doesn't have to use a new method 

// OKAY PROBLEM SOVLED! (we can always change in post-1.0 release BTW) AND *IF* we change it we will have a reason to and thus example code that shows the problem.

class OrderLine_Collection_Generated {
	// proposed new NOTIFICATION method that parent objects can use.
	public function setOrderId($orderId) {
		// generator by default shall pass on the nofication as "sets" on the child objects (to enable "single save" feature
		foreach ($this as $element) {
			///////////////////////////
			// Plan (c)?
			// this plan has benefits b/c we only have one extra method instead of a bunch, BUT this method will become very complex??? complex in what way?
			// doesn't work. 
			// what would this method look like?
			///////////////////////////
			$element->handleParentKeyIdChange($field, $keyId);
				$setterName = 'set' . Cough::camelCase($field);  // making assumptions here.. be careful! the parent knows the fieldname that needs to change on the child right??
				$this->$setterName($keyid); // sucky programming when the parent already knows the name. JUST call the method then. My problem with that is only a philosophical one... the child should be in control of operations on itself like parent key id changes
				//DONE ok I know some parts of it are wrong but by pseudocode standard

				// NO, the parent OWNS the child. not the other way around. OrderLine, I own you. Change yourself damnit b/c I'm changing my name. lol.
			
			///////////////////////////
			// or Plan (d)?
			// simple, re-uses existing methods.
			// doesn't allow you to override easily though.
			///////////////////////////
			$element->setOrderId($orderId);
			
			
			///////////////////////////
			// or Plan (e)?
			///////////////////////////
			//$element->handleParentOrderIdChange($orderId); // lzhang 10/16: I killed this plan

			
			// "THERE CAN BE ONLY ONE" PARENT at runtime, but the actual parent could be several different  not with instance pool... here's the thing, maybe it doesn't matter b/c you are just telling it a new ID is available. order line does not need to know which INSTANCE in memory is making the request, only that a new ID is available. ok wait is orderIdddddddddddd is that the name of the field on the parent or on the child... it has to be the child right? yes, child (see below, you said "I agree the latter"). Please don't hold me to things I said 10+ minutes ago AIGHT it is good... the reason why I like it this way is now I can override handleParentOrderIdChange on OrderLine and not ever worry about setOrderId sure that's a good point LOL METHODS ARE AWESOMEEEE IT IS THE SAME REASON WHY I ADD FACTORYBYID ON EVERY OBJECTTTT
			// sure but would there ever be a need? REMEMBER, only solve problems we have. When have you ever needed to change the way "single save" works now? Keep in mind we are talking about extra overhead here because we don't re-use the already existing setOrderId and are generating YET ANOTHER method to do the same thing almost always.
			// USEFUL METHODS are awesome. LAME METHODS are not.
			// factory NOOOOOOOOOOO lol.
			
			
			
			// which key? there could be several? handleOrderIdChange() ?
			// hmm yes generate several (yes b/c you might want different behavior for them) BUT WHICH PARENTTTT is that an issue?
			
		}
	}
	// your thoughts?
	// One note: we should keep the method on the collection the same as the children or the parent object? (e.g. what to name it when it's order.order_id to order_line.foo_id; do we name it setOrderId or setFooId on the collection? I believe in the latter, because what if it is foo_id for a reason, e.g. if you went insane and also added an order_id column which is not the proper FK even though it's name suggests it.)
	// I agree, the latter (cool)
}








$order = Order::retrieveByPk(543543);
$order->loadOrderLine_Collection();
$order->setKeyId(343);
	// nothing happens under plan (a)
	// but plan (b) says it should notify the order line collection (AND all other un-loaded collections that have an order_id)
	
???

$order->getOrderLines()->getFirst()->getOrderId() == ?????

class CoughObject {

	// ...
	
	/**
	 * If the object has a parent, then this method will update its foreign
	 * key with the value from the parent's primary key.
	 * 
	 * It's called automatically by {@link save()}.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function setFieldsFromParentPk() {
		if ($this->hasCollector()) { // <-- PROBLEM IS HERE
			$this->setFieldsIfDifferent($this->getCollector()->getPk());
		}
	}
	
	// The above method only gets used in save():
	
	/**
	 * Creates a new entry if needed, otherwise it updates an existing one.
	 * During an update, it only updates the modified fields.
	 * During an insert, it only inserts modified fields (leaving defaults up to
	 * the database server).
	 * 
	 * @return boolean - the result of the create/update.
	 * @author Anthony Bush
	 **/
	public function save() {
		
		// Don't save if deleted.
		if ($this->isDeleted()) {
			return false;
		}
		
		// Update the child with it's parent id
		$this->setFieldsFromParentPk();
		
		// Check for valid data.
		if ( ! $this->validateData()) {
			return false;
		}
		
		// Save self first, in case the PK is needed for remaining saves.
		if ($this->shouldInsert()) {
			$result = $this->insert();
		} else {
			$result = $this->update();
		}
		$this->isNew = false;

		$this->saveLoadedCollections();
		
		$this->resetModified();
		
		return $result;
	}



	// ...

}


?>

