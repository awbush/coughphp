<?php

class Schema {
	
	/**
	 * Stores the databases (SchemaDatabase) that have been loaded into
	 * memory so far.
	 *
	 * @var string
	 **/
	protected $databases = array();
	
	/**
	 * Returns the databases that were loaded from
	 * the server's DSN.
	 *
	 * @return array
	 **/
	public function getDatabases() {
		return $this->databases;
	}
	
	/**
	 * Get the specified database name, or null if it hasn't been set yet.
	 *
	 * @return mixed
	 * @author Anthony Bush
	 **/
	public function getDatabase($dbName) {
		if (isset($this->databases[$dbName])) {
			return $this->databases[$dbName];
		} else {
			return null;
		}
	}
	
	/**
	 * Add a database to the pile.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function addDatabase($database) {
		$this->databases[$databases->getDatabaseName()] = $databases;
	}
	
	
	/**
	 * Traverses all tables and uses any Foreign Key information to generate
	 * the relationships (one-to-one, one-to-many, and many-to-many) so that
	 * external entities (like the CoughGenerator) will only have to worry about
	 * what they want to *do* with the data rather than how to determine
	 * relationships.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function linkRelationships() {
		// Loop through the databases' tables, and use any FK information to build all the relationships.
		
		
		// foreach ($this->databases as $dbName => $db) {
		// 	$this->schemas[$dbName] = array();
		// 	foreach ($db->getTables() as $tableName => $table) {
		// 		$this->schemas[$dbName][$tableName]['primary_key'] = $table->getPrimaryKey();
		// 		$this->schemas[$dbName][$tableName]['columns'] = $table->getColumns();
		// 		
		// 		// get belongs to one relationships in the current database/schema
		// 		$primaryKey = $table->getPrimaryKey();
		// 		foreach ($table->getColumns() as $columnName => $column) {
		// 			if ($this->isForeignKey($columnName)) {
		// 				// If we have a multi-key PK (or no PK), search for related table.
		// 				if (count($primaryKey) != 1 || !isset($primaryKey[$columnName])) {
		// 
		// 				}
		// 
		// 				foreach ($db->getTables() as $relatedTableName => $relatedTable) {
		// 					if ($relatedTableName != $tableName) {
		// 
		// 					}
		// 				}
		// 			}
		// 		}
		// 		$this->schemas[$dbName][$tableName];
		// 		$this->schemas[$dbName][$tableName]['belongs_to_one'] = $table->getColumns();
		// 
		// 		// TODO: get belongs to one relationships for other databases/schemads
		// 						
		// 		
		// 	}
		// }
	}
	
	// // TODO: Split out into configuration options
	// protected $idSuffix = '_id';
	
	// /**
	//  * Returns whether or not the given dbColumnName is is a foreign key.
	//  *
	//  * @return boolean true if given column name is a foreign key, false if not.
	//  * @author Anthony Bush
	//  **/
	// protected function isForeignKey($dbColumnName) {
	// 	return (substr($dbColumnName, -strlen($this->idSuffix)) == $this->idSuffix
	// 		&& (strpos($dbColumnName, '2') === false));
	// }
	
	
}

?>