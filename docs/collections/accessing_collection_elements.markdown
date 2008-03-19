Accessing a Collection's Elements
=================================

Check if a collection is empty
	
	if ($books->isEmpty()) {
		echo 'It's empty';
	}

Get the count/number of elements

	echo 'There are ' . count($books) . ' elments in the collection.';

Loop through all the elements

	foreach ($books as $book) {
		echo $book->getTitle() . "\n";
	}

Get an element at a specific key

	// Using brackets
	$book = $books[$bookId];
	
	// Using the method (required for multi-key PKs)
	$book = $books->get($bookId);

Get an element at a specific position (ignoring the keys)

	// Get the first element
	$firstBook = $books->getPosition(0);
	
	// Get the last element
	$lastBook = $books->getPosition(count($books) - 1);
