
1. What is the expected API given some known data models.  List the accessors (getters & setters) as well as provide usage examples.

2. How do we implement the API simply and efficiently.

The product, product2os, and os "many-to-many" example:

Product
	Product2Os_Collection
		Product2Os object
		Product2Os object
		Product2Os object
		Product2Os object
			getCollector() returns the Product object by ref
			getProduct_Object() returns the Product object by ref
			getOs_Object() returns the Os object by ref
				getCollector() returns the Product2Os object by reference. Here in lies the problem: CoughInstancePool will not work if an object of the same ID has a method that returns different values based on how the object in question was retrieved (in this case from a many-to-many relationship with Product) We can't solve this by using distinct naming either, e.g. using getProduct2Os_Object() instead of getCollector().  The reason is that the relationship is many-to-many.  The same OS object could be linked to several product objects, and if we pull those products in memory at the same time then which Product2Os object should be returned?

The order and order_line "one-to-many" collection:

Order
	OrderLine_Collection (one-to-many example)
		OrderLine object
		OrderLine object
		OrderLine object
		OrderLine object
			getCollector() returns the Order object
			getOrder_Object() returns the Order object

For (2) we may:

a) create collections for each type of relationship, e.g. Product_Product2Os_Collection which implies a Product2Os_Collection for a Project object.  This special subclass could contain the extra methods specific to a Product's Product2Os_Collection.

a) avoid using the populateCollection method and instead create the end objects manually.  For example, we could right the Product::loadProduct2Os_Collection() method like so:

	$collection = new Product2Os_Collection();
	$collection->setCollector($this, 'setProduct_Object');
	
	$sql = '...';
	$result = $this->db->query($sql);
	while ($row = $result->getRow()) {
		$collection->add($product2os = Product2Os::constructByFields($row));
			// Notice that the collection's add method could automatically set the collector of any added children, but it doesn't know to set the Product reference (unless we keep the second parameter in CoughCollection::setCollector() as shown above)... so we'd have to do it:
			$product2os->setProduct_Object($this);
	}
	
	$this->setProduct2Os_Collection($collection);
