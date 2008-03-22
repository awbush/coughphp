Cough Handbook
==============

1. What is Cough?
2. Cough in Action
3. Dependencies
4. Installation
5. Generating Cough Classes
6. Using Cough Classes
	a. Basic Usage
	b. Creating
	c. Collections
	d. Related Objects
		i. Has One
		ii. Has Many
		iii. Has and Belongs to Many
	e. Performance Concerns
7. Additional Features
	a. Autoloader
	b. Query Helpers
8. Versions
	a. 1.1
	b. 1.2


What is Cough
-------------

Cough is an ORM (Object Relation Mapping) tool for mapping relational database schemas to objects.

Cough In Action
---------------

Assuming we have the schema:

	author
	------
	author_id
	first_name
	last_name
	most_popular_book_id  FK -> book.book_id
	
	book
	----
	book_id
	title
	isbn
	date_published
	
	authored_book         (many-to-many join)
	-------------
	book_id               FK -> book.book_id
	author_id             FK -> author.author_id
	author_sort_order     order to show authors for a book
	book_sort_order       order to show books for an author
	
	favorite_book         (many-to-many join)
	-------------
	book_id               FK -> book.book_id
	author_id             FK -> author.author_id
	rating
	
	alias                 (one-to-many)
	-----
	alias_id
	author_id             FK -> author.author_id
	alias

### Cough In Action: Objects ###

Create a new author:

	<?php
	$author = new Author();
	$author->setFirstName('Tony');
	$author->setLastName('Bush');
	$author->save(); // returns true on success
	?>

Get the author ID:

	<?php
	$authorId = $author->getAuthorId();
	?>

Retrieve an author from the database:

	<?php
	$author = Author::constructByKey($authorId);
	if (is_object($author)) {
		echo 'Author: ' . $author->getLastName() . ', ' . $author->getFirstName() . "\n";
	}
	?>

Update the author:

	<?php
	$author->setFirstName('Anthony');
	$author->save();
	?>

Delete the author:

	<?php
	$author->delete();
	?>

Get an author's most popular book (one-to-one example):

	<?php
	$book = $author->getMostPopularBook_Object();
	if (!is_object($book)) {
		echo 'No popular book set for ' . $author->getFirstName() . "\n";
	}
	?>

Get all aliases of an author (one-to-many example):

	<?php
	$aliases = $author->getAlias_Collection();
	echo 'Aliases for ' . $author->getFirstName() . ' ' . $author->getLastName() . ': ';
	if ($aliases->isEmpty()) {
		echo 'none';
	} else {
		$aliasNames = array();
		foreach ($aliases as $alias) {
			$aliasNames = $alias->getAlias();
		}
		echo implode(', ', $aliasNames);
	}
	?>

Get all books written by an author (many-to-many example):

	<?php
	$joins = $author->getAuthoredBook_Collection();
	$joins->sortByMethod('getBookSortOrder', SORT_ASC);
	foreach ($joins as $join) {
		$book = $join->getBook_Object();
	}
	?>

Get an author's favorite books (many-to-many example):

	<?php
	$joins = $author->getFavoriteBook_Collection();
	$joins->sortByMethod('getRating', SORT_DESC);
	foreach ($joins as $join) {
		$book = $join->getBook_Object();
		$rating = $join->getRating();
	}
	?>

The above two examples show one reason why you have to go through the join to get to the other object.  If there were to be a `getBook_Collection` method on the `Author` object, then how would you know which collection you were getting, favorite books or authored books?  Getting the extra data on the join table (such as `sort_order` and `rating`) would also be more challenging.  The good thing is that only one query will be run to pull both the join and book data.

Getting all authors that wrote a book can be done in the same manner (many-to-many example):

	<?php
	$book = Book::constructByKey(1);
	$joins = $book->getAuthoredBook_Collection();
	$joins->sortByMethod('getAuthorSortOrder', SORT_ASC);
	foreach ($joins as $join) {
		$author = $join->getAuthor_Object();
	}
	?>

#### Cough In Action: Objects: Advanced Techniques ####

Construct an author object using pre-loaded data:

	<?php
	$authorData = array(
		'author_id' => 1,
		'first_name' => 'Anthony',
		'last_name' => 'Bush'
	);
	$author = Author::constructByFields($authorData);
	?>

Retrieve an author using custom SQL:

	<?php
	$sql = '
		SELECT
			*
		FROM
			author
		WHERE
			first_name = "Anthony"
			AND last_name = "Bush"
		LIMIT 1
	';
	$author = Author::constructBySql($sql);
	?>

Best practices note:  Put custom SQL for an object as another `constructBy` method, like so:

	<?php
	class Author extends CoughObject implements CoughStaticInterface {
		public static function constructByName($firstName, $lastName) {
			$db = self::getDb();
			$sql = '
				SELECT
					*
				FROM
					author
				WHERE
					first_name = ' . $db->quote($firstName) . '
					AND last_name = ' . $db->quote($lastName) . '
				LIMIT 1
			';
			return Author::constructBySql($sql);
		}
	}
	?>

You can use the above method just like the other `constructBy` methods:

	<?php
	$author = Author::constructByName('Anthony', 'Bush');
	if (is_object($author)) {
		echo 'Found author Anthony Bush!';
	}
	?>

### Cough In Action: Collections ###

Retrieve all authors in the database:

	<?php
	$authors = new Author_Collection();
	$authors->load();
	?>

Retrieve authors with custom SQL:

	<?php
	$sql = 'SELECT * FROM author WHERE last_name = "Bush"';
	$authors = new Author_Collection();
	$authors->loadBySql($sql);
	?>

Check if the collection is empty:

	<?php
	$isEmpty = $authors->isEmpty();
	$isEmpty = count($authors) == 0;
	// does not work: empty($authors);
	?>

Loop through all authors in the collection:

	<?php
	foreach ($authors as $authorId => $author) {
		echo 'Author ' . $author->getAuthorId() . ': '
		echo $author->getLastName() . ', ' . $author->getFirstName() . "\n";
	}
	?>

Get an author with a specific key:

	<?php
	$authors->get($authorId); // returns null if not found
	?>

Get an author at a specific position (regardless of key values):

	<?php
	// First author
	$authors->getPosition(0);
	// Last author
	$authors->getPosition(count($authors) - 1);
	// Random author (2 ways)
	$authors->getPosition(rand(0, count($authors) - 1));
	$authors->get(array_rand($authors));
	?>

### Cough In Action: Collections: Advanced Techniques ###

Manually build a collection with pre-loaded data:

	<?php
	$authorsData = array(
		array(
			'author_id' => 1,
			'first_name' => 'Anthony',
			'last_name' => 'Bush'
		),
		array(
			'author_id' => 2,
			'first_name' => 'Lewis',
			'last_name' => 'Zhang'
		)
	);
	$authors = new Author_Collection();
	foreach ($authorsData as $authorData) {
		$authors->add(Author::constructByFields($authorData));
	}
	?>

The main time doing something like the above would be useful is when pulling data that doesn't all belong to the same thing in one query.  For example, we could pull all the authors in the database with one query, then all the author aliases in the database with a second query, then merge the data.  We might add a method to the Author_Collection object that looks like this:

	<?php
	class Author_Collection extends CoughCollection {
		public function loadAuthorsAndTheirAliases() {
			// Load authors
			$this->load();
			// Load aliases and add them onto the authors
			$db = Alias::getDb();
			$sql = 'SELECT * FROM alias ORDER BY author_id';
			$db->selectDb(Alias::getDbName());
			$result = $db->query($sql);
			$lastAuthor = null;
			$lastAuthorId = null;
			while ($row = $result->getRow()) {
				if ($lastAuthorId != $row['author_id']) {
					if (is_object($lastAuthor)) {
						$lastAuthor->setAlias_Collection($aliases);
					}
					$lastAuthor = $this->get($row['author_id']);
					$lastAuthorId = $row['author_id'];
					$aliases = new Alias_Collection();
				}
				$aliases->add(Alias::constructByFields($row));
			}
			if (is_object($lastAuthor)) {
				$lastAuthor->setAlias_Collection($aliases);
			}
		}
	}
	?>

Dependencies
------------

* PHP5 (any version?)
* MySQL (it should be possible to hook up other database drivers, but out of the box only support for MySQL is included)

Installation
------------

Extract the zip.  In your application, just include the core `load.inc.php` file.  If you want to use the `As_Database` module, then include it as well.  What follows is an example that also configures the database information:

	<?php
	include_once('modules/coughphp-1.1/cough/load.inc.php');
	include_once('modules/coughphp-1.1/as_database/load.inc.php');
	CoughDatabaseFactory::addConfig(array(
		'aliases' => array('main_db'),
		'driver' => 'mysql',
		'host' => 'localhost',
		'user' => 'nobody',
		'pass' => '',
		'port' => 3306
	));
	?>

See the CoughDatabaseFactory API for more options.

We recommend using an autoloader of some sort as well.  We've included one that has path caching features so that it only has to scan for a class's location once.  See the Autoloader section for usage info.

Generating Cough Classes
------------------------

Use the cough executable in the scripts folder:

	cd /path/to/cough/
	./scripts/cough

For example configurations, see the `config_examples` folder.  To get up quick, just duplicate the default folder and change the database settings in the `database_schema_generator.inc.php` file.  For more advanced examples, see the `more` config example.

Using Cough Classes
-------------------

We may need to move stuff from "Cough In Action" into here, or just go into more depth here.

Additional Features
-------------------

### Autoloader ###

Use the autoloader because it's awesome and uses caching for speed.  It's in the `extras` folder.  Use it like so:

	<?php
	include_once('modules/coughphp-1.1/extras/Autoloader.class.php');
	Autoloader::addClassPath('/path/to/generated/models/');
	Autoloader::setCacheFilePath('/path/to/cache/class_path_cache.txt');
	Autoloader::excludeFolderNamesMatchingRegex('/^CVS|\..*$/');
	spl_autoload_register(array('Autoloader', 'loadClass'));
	?>

This means you won't have to include files yourself and can focus on your application logic.  You can have it scan as many paths as you like, even your own non-Cough classes.  It scans directories recursively, so once you add a path, you don't have to add any of the directories inside that path.

You may have to change permissions on the cache file to be writable:

	cd /path/to/cache/
	chmod a+rw class_path_cache.txt

### Query Helpers ###

Cough loads query helpers by default.  The most useful of them all is the `As_SelectQuery` class.  You can use it to build SQL incrementally.  It's the best way to ensure you don't copy the same SQL over and over just to change one thing in the WHERE clause or the ORDER BY statement.

Here's an example:

	<?php
	public function Author extends CoughObject implements CoughStaticInterface {
		public function getLoadSql() {
			$sql = new As_SelectQuery(self::getDb());
			$sql->addSelect('author.*');
			$sql->addFrom('author');
			$sql->setOrderBy('author.last_name, author.first_name');
			return $sql;
		}
		public function constructByName($firstName, $lastName) {
			$sql = self::getLoadSql();
			$sql->addWhere(array(
				'author.first_name' => $firstName,
				'author.last_name' => $lastName,
			));
			return self::constructBySql($sql);
		}
	}
	?>

Notice how in our custom `constructByName` we build upon the SQL set in the `getLoadSql` method.  Not all that useful in this simple example, but imagine if we had joins to other tables, selected other values, and had other where clauses that needed to be reused.

For more, see the `As_SelectQuery` and `As_Query` class documentation.  The methods are very flexible and can take a variety of parameters.  For example, `addWhere` can take strings or arrays.

Some Other Section
------------------

Documentation here has yet to be filed under the appropriate section.  It was mostly thought of while writing another section but goes into more detail than was appropriate for the section being written.

### Retrieving The Primary Key ###

This section uses the author example from Cough In Action.

We can get the primary key a number of ways:

	<?php
	$author->getPk(); // returns array('author_id' => value);
	$authorId = $author->getKeyId(); // returns just the value
	$authorId = $author->getAuthorId(); // returns just the value
	?>

Each of the above methods are slightly different:

* `getPk` always returns an array (hash of key => value pairs).
* `getKeyId` returns a comma separated list of the values making up the primary key.  (This means it will be the same as the direct accessor when there is only one column making up the primary key.)
* `getAuthorId` is a direct accessor to the `author_id` field.

