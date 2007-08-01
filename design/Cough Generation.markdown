Cough Generation
================

Question: Do we use one config array/object for all generation classes and let them choose which configuration options they want to use or do we separate configuration?

Some of this question lies within where each of the generation classes responsibility lies, but even if we clearly define separate responsibilities, we might still want a shared configuration...

With that, lets go ahead and define the responsibilities:

SchemaGenerator (or maybe CoughSchemaGenerator to avoid naming collisions)
---------------

A Schema Generator is reponsible for creating a Schema object. It's input is undefined, so that it may take a file name (e.g. XML file for the XML Driven Schema Generator) or an array containing database configuration information (e.g. Database Driven Schema Generator). The point is that they all support a `generateSchema()` method which should return a Schema object (or throw an Exception if no Schema can be generated).

Schema (or maybe CoughSchema to avoid naming collisions)
------

Representation of the structure of a single database. This includes tables, the table's columns, and relationship between the tables.

Our requirements of the Schema are that it implements a particular interface:

	getDatabase()
		getDatabaseName()
		getTables()
			getDatabase() -- reference to parent
			getTableName()
			getPrimaryKey() -- returns columns that take part in the PK
			getColumns()
				getColumnName()
				isNullAllowed()
				getDefaultValue()
				getType()
				getSize()
				isPrimaryKey()

CoughGenerator
--------------

Takes a single Schema and generates a collection (array) of CoughFile objects.

CoughFile
---------

Capable of writing itself to disk, a CoughFile is simply a filename, its (soon to be) contents, and any metadata Cough needs (e.g. whether or not it is a "starter" file).

CoughWriter (or CoughFileHandler ?)
-----------

Takes a collection (array) of CoughFile objects and chooses which ones to write to disk.

It is also capable of providing information of what changes would take effect before making them (e.g. files that might be removed, added, modified).

CoughConfig
-----------

Do we need a class to manage the configuration settings? It seems like we might need to move away from the single array in one config file to several config files. For example:

	config/
		default/
			DatabaseSchemaGenerator.config.php
			CoughGenerator.config.php

DatabaseSchemaGenerator.config.php would probably contain configuration for the databases and tables to scan.

CoughGenerator.config.php would probably contain information on how to name the generated classes "_Collection", etc.

	config/
		default/
			schema_generator.conf.php
			<xml_schema.xml> -- if the schema generator to be used is the Xml one...
			<database_schema_generator.conf.php> -- if the schema generator to be used is the Database one...
			cough_generator.conf.php



Usage Samples
-------------

We *could* make a factory that creates the correct schema generator object...

	<?php
	$schema = SchemaGeneratorFactory::getSchemaGenerator($coughConfig); // ?
	?>

	<?php
	// Database Schema Generator example
	include_once(CONFIG_DIR . 'database_schema_generator.conf.php');
	$schemaGenerator = new DatabaseSchemaGenerator($config);
	$schemaGenerator->generateSchema();
	?>


There will of course be assistants that already know how all the parts interact as a hole. Among these will be:

* A command line script
	* Can show status (added, removed, and modified files) without actually writing anything to disk.
	* Can (re)-generate files.
	* Will use config files.
* A web panel
	* Same as command line script, but with a GUI.
	* Will use config files.
* A wizard
	* Will assist in the modification of config files.






Checklist for implementation
----------------------------

Besides any missing documentation, the Schema needs:

* DriverColumn
* MysqlColumn
* ServerColumn

Columns in general need relationship or FK checking if that is not going to be done on the table level...

* DriverServer
* MysqlServer
* ServerServer

* DriverTable
* MysqlTable
* ServerTable

* DriverDatabase
* MysqlDatabase
* ServerDatabase

Next Up:

* What is interface for retrieving relationships between objects? 

Just thinking, but maybe:

	<?php
	foreach ($schema->getDatabases() as $database) {
		foreach ($database->getTables() as $table) {
			// This tables data
			$columns = $table->getColumns();
			$pk = $table->getPrimaryKey();
			
			// Other tables that have this one's key 
			foreach ($table->getHasOneRelationships() as $relationship) {
				
				$relationship = array(
					array($table->getColumn($col1), $relatedTable->getColumn($col2)),
					// Most will only have one id -> one id, but we can allow more than one via:
					// array($table->getColumn($col3), $relatedTable->getColumn($col4)),
				);
				// Um, how do we know which keys relate?
				// We just want what columns on $table link to what columns on $relatedTable.
				// ticket_line_id -> order_line_item_id, e.g. So we just need references...
			}
			
		}
	}
	?>



