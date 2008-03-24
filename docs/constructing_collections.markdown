Constructing Collections
========================

Collections can be retrieved in many ways, including:

* From a related object
* Through manual instantiation.
* Using custom SQL

From a related object
---------------------

	$author = Author::constructByKey($authorId);
	$books = $author->getBook_Collection();

Through manual instantiation
----------------------------

	$book = new Book_Collection();
	$book->load();

Using custom SQL
----------------

	$book = new Book_Collection();
	$book->loadBySql($sql);

Building on the base SQL
------------------------

Building on the base SQL is currently somewhat of a challenge, as it requires knowledge of what parts of the SQL the base includes.  As such, it might be necessary to use PHP's string functions to replace parts of the SQL.

Assuming the base SQL doesn't include an ORDER BY clause, the following would work:

	$book = new Book_Collection();
	$sql = $book->getLoadSql();
	$sql .= ' ORDER BY book.title DESC LIMIT 100';
	$book->loadBySql($sql);

In the future, it could work like this:

	$book = new Book_Collection();
	$sql = $book->getLoadSql();
	$sql->setOrderBy('book.title DESC');
	$sql->setLimit(100);
	$book->loadBySql($sql);
