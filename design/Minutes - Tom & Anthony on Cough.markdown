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

* Write deflate and inflate
	* nothing in Cough calls deflate, not even clone. It's there for user... TODO: Tom: Write usage examples
	* inflate called by constructor (2 params).
* Write fieldDefintions usage (and define function)
* Write derivedFieldDefintions usage (ditto)
* Put back defineDbConfig() // dbName and tableName + the @todo why
* Change constructor to include an optional second parameter $relatedEntities
* Put `@todo Tom: Why this design decision?` sections where we made decisions that others might not understand

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



