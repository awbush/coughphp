<?php

$orderB->addOrderLine($orderA->getOrderLine_Collection());
// Besides the fact that this won't work in the new Cough because add requires that you add an individual element, the idea here is that all the order line objects for one collection should be nulled out during the add process and they should be updated to the with order id = $orderB->getOrderID() when orderB is saved. The latter part is done already, but the first part (nulling out) is not.

// Rather than re-add all the crazy functions required to make adding multiple elements in the same function call work, we should require that the Cough user provide their own loop:

foreach ($orderA->getOrderLine_Collection() as $orderLine) {
	$orderB->addOrderLine($orderLine);
}
$orderB->save();

// Doing this is not "extra" work for the Cough user because it is clearer "addOrderLine" is not plural and thus should suggest that you can't add a collection, you have to do the loop yourself. The fact that Cough users everywhere get to benifit from a lighterweight ORM is a side-effect.

?>