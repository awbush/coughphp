---
title: CoughPHP Changelog
---

Changelog
=========

1.3.5 (2008-12-19)
------------------

* Ported [`getFieldsThroughGetters()`]({{ site.baseurl }}/docs/1.3.5/api/cough/CoughObject.html#getFieldsThroughGetters), [`setFieldsThroughSetters()`]({{ site.baseurl }}/docs/1.3.5/api/cough/CoughObject.html#setFieldsThroughSetters), and test cases from A.S. branch.

* Updated copyright information.

<a href="{{ site.baseurl }}/files/coughphp-1.3.5.tgz" class="track_link">Download 1.3.5 release</a>

1.3.4 (2008-11-12)
------------------

* Fixed [bug 297308](https://bugs.launchpad.net/coughphp/+bug/297308): "hasKeyId returns wrong value."

* Added ability to set SQL select options on `As_SelectQuery` objects (e.g. `SQL_CALC_FOUND_ROWS`, `SQL_NO_CACHE`).

* Added SQL needed to create test database and user in `tests/database_config.inc.php`.

<a href="{{ site.baseurl }}/files/coughphp-1.3.4.tgz" class="track_link">Download 1.3.4 release</a>

1.3.3 (2008-10-19)
------------------

* Fixed [bug 284702](https://bugs.launchpad.net/coughphp/+bug/284702): "bad code generation."

* Added `CoughCollection` methods [`getFirst()`]({{ site.baseurl }}/docs/1.3.3/api/cough/CoughCollection.html#getFirst) and [`getLast()`]({{ site.baseurl }}/docs/1.3.3/api/cough/CoughCollection.html#getLast).

* Enhanced error handling in `As_Database`.

<a href="{{ site.baseurl }}/files/coughphp-1.3.3.tgz" class="track_link">Download 1.3.3 release</a>

1.3.2 (2008-09-22)
------------------

* Added `As_Database::getUniqueQueryLog()`.

* Added `CoughDatabaseFactory::getUniqueDatabases()`.

* Added `CoughObject::isFieldModified()`.  Don't access the modifiedFields array directly anymore; use `isFieldModified()` instead.

* Changed `CoughObject::setFieldsIfDifferent()` so that it only sets fields that actually belong to the object (by checking fieldDefinitions and derivedFieldDefinitions first).

* Changed `Autoloader` so that saving of newly found classes is postponed until the script ends.  This keeps it from writing to the disk inefficiently on the first page view or anytime a number of new classes have been introduced.

* Added `Autoloader` unit test cases.

<a href="{{ site.baseurl }}/files/coughphp-1.3.2.tgz" class="track_link">Download 1.3.2 release</a>

1.3.1 (2008-08-28)
------------------

* Fixed [launchpad bug #262475](https://bugs.launchpad.net/coughphp/+bug/262475).  Test cases added.

* Changed: As_Query throws an Exception when quoting is needed but no DB object was passed during __construct.

* Added helper method CoughObject::buildSelectQuery(), see phpDoc block.

<a href="{{ site.baseurl }}/files/coughphp-1.3.1.tar.gz" class="track_link">Download 1.3.1 release</a>

1.3 (2008-08-22)
----------------

* Added: Can now specify `client_flags` parameter when using the "as" database adapter (default).  See [http://php.net/mysql_connect](http://php.net/mysql_connect)

* Added: PHP5 strict compatibility

* Added: Support for VIEWs (and any other table without a primary key).

* Added: `As_DatabaseResult::getRows()` (returns **all** rows found as an array of arrays).

* Added: `CoughDatabaseFactory::getDatabase()` now supports an optional second parameter of a database name to select before returning the object.

* Added: Unit test cases are now included in the download (remove the "tests" directory if you want to save space, ~400K).

* Changed: Calling `CoughDatabaseFactory::getDatabase('db_alias')` with an invalid alias now throws a verbose exception instead of returning null.

* Changed: Re-ordered CoughObject's attributes so that data shows up before definitions.  Readability of `print_r()` output and similar functions is thus improved.

<a href="{{ site.baseurl }}/files/coughphp-1.3.tar.gz" class="track_link">Download 1.3 release</a>

1.2 (2008-06-22)
----------------

* Changed the code generator: Generated SQL now uses the static methods `getDbName()` and `getTableName()` so that the static `$dbName` and `$tableName` member variables can be overridden in concrete classes without also having to override all the generated `getLoadSql()` and `load*_Collection()` functions.

* Added support for environment specific database names.  See the installation instructions in the <a href="http://coughphp.anthonybush.com/docs/1.1/handbook/">CoughPHP handbook</a>.

<a href="{{ site.baseurl }}/files/coughphp-1.2.tar.gz" class="track_link">Download 1.2 release</a>

1.1.2 (2008-04-25)
------------------

* Added "Debugging Queries" documentation.
* Added: When logging queries, the database that each query is run on is also logged.
* Added `As_Database::getError()` to the database object.
* Cleaned up phpdocs in `CoughCollection`.

<a href="{{ site.baseurl }}/files/coughphp-1.1.2.tar.gz" class="track_link">Download 1.1.2 release</a>

1.1.1 (2008-04-15)
------------------

* Fixed `As_Database::insertMultiple()` -- it did not work, but isn't used internally by Cough anyway.
* Enhanced `CoughObject::getFieldAliases()` so that the table name to SELECT from can be specified; this is needed when aliasing the tables in the FROM clause.
* Changed `As_Query` so that it throws an Exception when there is no database object (instead of hitting a PHP Fatal Error).

<a href="{{ site.baseurl }}/files/coughphp-1.1.1.tar.gz" class="track_link">Download 1.1.1 release</a>

1.1 (2008-03-23)
----------------

* Official 1.1 release.

<a href="{{ site.baseurl }}/files/coughphp-1.1.tar.gz" class="track_link">Download 1.1 release</a>

1.1-prerelease (Jan 2008)
-------------------------

* Changed the code generator: completely overhauled with re-usable components and advanced configuration possibilities.
* Changed the way objects are constructed: now use only static/factory methods to construct objects (This allows the appropriate sub-class to be constructed).
* Added ability to use any database abstraction layer (DAL), such as PDO.
* Bug fixes.

1.0 (April 2007)
----------------

* First public release after a year in production use on enterprise e-commerce websites and on our own personal projects.
