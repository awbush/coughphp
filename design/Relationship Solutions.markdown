Change generated loaders from this:
	
<?php

public function loadProductOrder_Collection() {

	// What are we collecting?
	$elementClassName = 'ProductOrder';

	// Get the base SQL (so we can use the same SELECT and JOINs as the element class)
	$element = new $elementClassName();
	$sql = $element->getLoadSqlWithoutWhere();

	// What criteria are we using?
	$criteria = array(
		'customer_id' => $this->getId(),
	);
	$sql .= ' ' . $this->db->generateWhere($criteria);

	// Construct and populate the collection
	$collection = new ProductOrder_Collection();
	$collection->setCollector($this, CoughCollection::ONE_TO_MANY); // TODO: Anthony: Remove type? The collection object should not need this info anymore... (and, FYI, all collections are one-to-many -- it only differs when we provide "direct access" to table2 without having to go through a join table.)
	$collection->populateCollection($elementClassName, $sql);
	$this->setProductOrder_Collection($collection);
}

?>
	
To this:

<?php

public function loadProductOrder_Collection() {
	
	$sql = '
		SELECT
			`product_order`.*
			, `product`.`category` AS `product.category`
			, `product`.`id` AS `product.id`
			, `product`.`price` AS `product.price`
		FROM
			`product_order`
			INNER JOIN `product` ON `product_order`.`product_id` = `product`.`id`
		WHERE
			`product_order`.`customer_id` = ' . $this->db->quote($this->getId()) . '
	';

	// Construct and populate the collection
	$collection = new ProductOrder_Collection();
	$collection->setCollector($this, 'setCustomer_Object');
	$collection->populateCollection('', $sql);
	$this->setProductOrder_Collection($collection);
}

?>