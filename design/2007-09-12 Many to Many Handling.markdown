<?php

$product = new Product(1);

// Pull Mac and Win os objects....
foreach ($product->getOs_Collection() as $os) {
	// We are disabling these for 2 reasons: 1) keep instance pool working 2) simply Cough's job
	// $os->getJoinField('enrollment_start_date');
	// $os->getJoin_Object();
	// $os->getProduct2Os_Object();
	// in fact, can't get join data at all when going through convenience method.
}


// Want join data? Have to go through join collection...
foreach ($product->getProduct2Os_Collection() as $product2os) {
	$os = $product2os->getOs_Object();
}

// Don't do convenience methods at all due to conflicts. Just make the user write them on their own.

// Also we need to address saving issues; previously we did not save loaded objects, but now if we may have to (at least the join object's objects). For example
$product->save();
	// save's any product row changes
	// saves all checked collections, the Product2Os collection in this case
		// For each one of those product2os table is updated if needed, BUT we don't save the os object itself :(
			// One solution is to add an isJoinObject attribute and have save do something like:
				if ($this->isJoinObject()) {
					$this->saveLoadedObjects();
				}



?>




