ORM / Model: Cough
------------------

1. A way to get and set fields from a persistent layer.

2. A way to ensure fields meet certain validation criteria before being allowed into the persistent layer. {
	* A way to easily set customer validation error messages that can be passed to the view later.
		* e.g. "Passwords do not match", "Username is already taken", "Password must contain at least 6 characters"
}

3. A way to manage related objects {
	* A way to easily construct the related object from the FK.
		* Ability to optimize query so that one query pulls an object and all the related data.
}

4. A way to manage related collections of objects (one-to-many) {
	* Easily pull an entity's related collections with no SQL knowledge.
		* Easily customize which data is retrieved or how data is retrieved using what you already know about SQL.
	* Easily add and remove entities from a collection with an id or object.
		* Easily add or override additional functionality to adders/removers to perform custom business logic.
}

5. A way to manage related collections of objects (many-to-many) {
	* Same as one-to-one, even looping through the collection (i.e. no need to loop through join table collection), plus
	* A way to manage additional join fields easily.
	* Supports retiring of columns (aka delete_flag).
	* Supports DELETEs in addition to the above.
}










Make this clear
---------------

Basically, some marketing things for the web site.

* Rules of OO programming

* High performance
	* Will not do needless loads


















### 1. Getting/Setting Fields ###
get<field>() // gets the field. easy to override.
set<field>($value) // sets the field and toggles a modified flag so that save works. easy to override

### 2. Validation Functions ###
doValidateData(&$fields) // validate passed in data.
invalidateField(<field>, <message>)
getValidationErrors()
// etc...

### 3. Manage related objects ###
get<object>_Object() // get an object, initializing it if needed.
set<object>_Object() // set an object to a value you know. override if you want it to also set the FK or supported out of box?

### 4. Manage related one-to-many collections ###
get<collection>_Collection()

### 5. Manage related many-to-many collections ###
get<collection>_Collection() // careful here... we ran into some naming conflicts before when there was more than one way to join into a collection...this is cause for reconsidering they way many-to-many collections are iterated through.

person
	person_id name
school
	school_id name
student
	person_id school_id start_date end_date
school_rating
	person_id school_id rating

With this model person has two school collections, a collection of schools they were students at, and a collection of schools they rated. If we require that join tables be retrieved first, we solve the naming conflict problem. Example code:

	<?php
	$person = new Person(1);
	foreach ($person->getSchoolRating_Collection() as $schoolRating) {
		$school = $schoolRating->getSchool(); // no extra query run by default on many-to-many queries like this.
		echo $person->getName() . ' rated ' . $school->getName() . ' ' . $schoolRating->getRating() . '.';
	}
	foreach ($person->getStudent_Collection() as $student) {
		$school = $student->getSchool(); // no extra query run by default on many-to-many queries like this.
		echo $person->getName() . ' attended ' . $school->getName()
		     . ' from ' . $student->getStartDate()
		     . ' to ' . $student->getEndDate() . '.';
	}
	?>

The terminology for adding/removing also makes more sense:

	<?php
	$person = new Person(1);
	
	// Old way:
	$person->removeSchool($schoolId); // which school collection gets an item removed from?
	
	// New way:
	$person->removeSchoolRating(/* BUT, what to pass? PK is what it currently expects, but doesn't it make more sense to pass the school_id? probably not, see next example */);
	
	// Let's using the remove on the join but passing the id of the PK of the other side of the join:
	$person->removeStudent($schoolId); // which record in the join gets removed? person might have been a student at the same school for many different date ranges. THUS, we must keep it simple (but maybe also confusing? See following examples)
	
	// Right way:
	$person->removeStudent($primaryKey); // either a separate primary key column, student_id, or multi-key PK for person_id, school_id, start_date, and end_date.
	
	?>

Okay, so what about adding?

	<?php
	$person = Person(1);
	$person->addSchoolRating($schoolRatingId); // makes no sense, we have no PK yet.
	$person->addSchoolRating($schoolId, $rating); // ?
	$person->addSchoolRating($schoolId, $joinFields = array('rating' => 0)); // ?
	?>

The above is confusing... The correct way to do it is the same as with one-to-many collections (basically everything is a "one-to-many" unless it is the direct access "many-to-many")

	<?php
	$person = Person(1);
	// Prototype: addSchoolRating($schoolRatingFields_or_schoolRatingObject);
	$person->addSchoolRating(array('school_id' => $schoolId, 'rating' => 0)); // ?
	
	$schoolRating = new SchoolRating();
	$schoolRating->setSchoolId($schoolId);
	$schoolRating->setRating(0);
	$person->addSchoolRating($schoolRating);
	?>


Okay, so how is adding join name confusing (i.e. why did we go the route that causes naming conflicts)? Well, consider the data model:

	product
		product_id name
	os
		os_id name
	product2os
		product2os_id product_id os_id

It makes a lot of since to say:

	<?php
	$product = new Product(1);
	$product->addOs($osId);
	// or
	$product->setOs_Collection(array($osId1, $osId2/*, etc. */));
	?>

But if we fix the naming conflicts it would then become:

	<?php
	$product = new Product(1);
	$product->addProduct2Os($osId, $joinFields=array());
	$product->setProduct2Os_Collection(array($osId1, $osId2/*, etc. */)); // ? kind of weird, isn't it? maybe we need a toggle point at the generator level... [ ] This is a simple join table, make it look like a one-to-many.
	?>

