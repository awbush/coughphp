ticket
	ticket_id
	customer_id (FK, NOT NULL)
	order_total
customer
	customer_id
	name


Standard one-to-one object, ticket has one customer.

GOAL: Pull ticket and customer in one query.

Easy SQL:

	SELECT
		ticket.*,
		customer.customer_id as `customer.customer_id`
		customer.name as `customer.name`
	FROM
		ticket
		INNER JOIN customer USING (customer_id)
	WHERE
		# whatever where we were using when just pulling the ticket...
		ticket_id = <PK>


NOW: How to instantiate ticket and set customer object?

	<?php
	$ticket = new Ticket($row);
	?>

Way 2: Isn't for getting stuff out of the database, really...

	<?php
	$ticket = new Ticket($ticketData, array('customer' => $object));
	$ticket = new Ticket($ticketData, array('customer' => new Customer($customerData)));
	?>


Show cms_component example.


	SELECT
		cms_component.*,
		cms_template.template_id as `cms_template.template_id`
		cms_template_type.template_type_id as `cms_template_type.template_type_id`
	FROM
		ticket
		INNER JOIN customer USING (customer_id)
	WHERE
		# whatever where we were using when just pulling the ticket...
		ticket_id = <PK>
	






NEW COUGH GENERATOR FUNCTIONALITY:
----------------------------------

1. If we see FK (and NOT NULL), the we will automatically generate the awesome join query with the related objects aliased
2. 

NEW COUGH FUNCTIONALITY
-----------------------

* DONE: Show cms one-to-one-to-one-to-one example
	* Pass in "creational buffer" -- stuff you didn't know what to do with to all other FK NON NULL objects.

Action Items For Anthony
------------------------

* DONE: Write deflate and inflate
	* nothing in Cough calls deflate, not even clone. It's there for user... TODO: Tom: Write usage examples
	* inflate called by constructor (2 params).
* DONE: Write fieldDefinitions usage (and define function)
	* TODO: What is format? (needed for generator)
* DONE: Write derivedFieldDefintions usage (ditto)
	* TODO: What is format?
* DONE: Put back defineDbConfig() // dbName and tableName + the @todo why
* DONE: Change constructor to include an optional second parameter $relatedEntities (just pass it on to infalte)
* Put `@todo Tom: Why this design decision?` sections where we made decisions that others might not understand
* We should be able to get rid of all the *join* functions in CoughObject since we will be generating a CoughObject and CoughCollection class for all those... Problem is with direct collection access, e.g.

	product
	product2os
	os
	
	<?php
	$product = new Product();
	$product->addOs(new Os());
	
	class Product {
		public function addOs($os, $joinData) {
			$os->setCollector($this);
			
			// later on save
			
			// (if adding)
			$joinData->setOsId($this->getOsId());
			$joinData->setProductId($this->getCollector()->getProductId());
			$joinData->save();
			
			// (if removing)
			$joinData->setIsRetired(1);
			$joinData->save();
				// That should really change to a separate function like "delete" or "retire" which can be overridden.
				$joinData->delete();
				// if a retired column is specified, update it:
				$this->setIsRetired(1);
				$this->save();
			
			
		}
	}
	?>

Provide examples of how to manage join fields in both direct access and non direct access for both "2" examples and non-"2" examles. This includes how adding and removing works (how to overridden or specified additional join field changes during add/removals) and how to set fields in general, in case I want to change a field during a non-add/removal operation.

Adding currently does:

* For one-to-many:
	* Sets the collector of the added element to the parent object (not the collection object).
	* TODO: Make sure the parent is available through named function, e.g. ticketLine::getTicket() should not run a query to get the ticket object since we already got it by reference.
		* Maybe just have the setCollector function loop through and find the setter function to call...

* For many-to-many:
	* Sets the collector, like one-to-many, and:
	
			$object->setJoinTableName($this->getJoinTableName());
			$object->setJoinFields($joinFields);
			$object->setIsJoinTableNew(true);
		
	* This information later gets used in the following manner:
		
		saveJoinFields()
			sets the FK fields from both tables:
				$this->getPk();
				$this->getCollector()->getPk();
			saves.

Adding should do:

* For one-to-many: same thing
* For many-to-many:
	* Same as one-to-many, and also:
	* Construct a join object (if one wasn't passed in) and call setFields on it (if an array was passed in).

I think we got the idea here... now how to manage existing fields in a non-add/removal operation:

Old way (with direct access; the only way to access in the old system):

	<?php
	$product = new Product(1);
	foreach ($product->getOs_Collection() as $os) {
		$os->setJoinField('avg_product_price', $newPrice);
	}
	$product->save(); // could also have just called saved on the $os object
	?>

New way?

* The above way will still work (for now), it'll just be implemented differently.
* You can also call: $os->getJoinObject()->setAvgProductPrice($newPrice);
* NO: You can also call: $os->getProduct2Os_Object()->setAvgProductPrice($newPrice);

So what about non-direct access?

	<?php
	$product = new Product(1);
	foreach ($product->getProduct2Os_Collection() as $join) {
		// $join->setField('avg_product_price', $newPrice);
		$join->setAvgProductPrice($newPrice);
		// $os = $join->getOs_Object();
	}
	$product->save(); // could also have just called saved on the $join object
	?>

So in the person -> student, professor -> school example, it is a little more readable:

	<?php
	$person = new Person(1);
	foreach ($person->getStudent_Collection() as $student) {
		$school = $student->getSchool_Object();
		// we now have join object ($student) and table2 object ($school)
	}
	?>

But, how does adding and removing work on non-direct access? Basically, works the same as any other one-to-many collection (the difference is in how the collection is retrieved; One query pulls the "one-to-many" collection in addition to the table2 entity)

	<?php
	$person = new Person(1);
	$person->removeStudent($studentIdOrObject); // Notice no second parameter, $schoolFields
	$person->addStudent($studentId, $table2Fields); // this doesn't make sense...
	
	// Without collection handling, adding a student would be like:
	
	$student = new Student();
	$student->setFields($person->getPk());
	$student->setFields($school->getPk());
	$student->setGpa(4.0);
	$student->save();
	
	// So maybe the function addStudent should take the table2 object/id and then student fields?
	
	$person->addStudent($schoolIdOrObject, $studentFields();
	
	?>

Collection TODOs
----------------

Generator based

* Generate classes (Object and Collection) for join2join tables (currently excluded based on appearance of "2")
* Provide accessors for join2join collections (and objects, if the PK is a field of some other table -- this would be interesting, is there an example?).
	* In addition to "direct" access where there is no conflict or a "2" ? or specified in schema...
		
		This needs an example. The Cough Generator might do something like:
		
			foreach ($this->getDirectAccessRelationships() as $relationship) {
				$code .= $this->generateRelationshipAccessors($relationship);
			}
* GENERATE joins in the queries automatically if FK constraint detected (or  not null? -- BTW, this is job of SchemaGenerator, not CoughGenerator)
	* LEFT JOIN to FK NULL
	* INNER JOIN to FK NOT NULL



