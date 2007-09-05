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
		
		// Build one-to-one and one-to-many relationships
		foreach ($this->getDatabases() as $dbName => $database) {
			foreach ($database->getTables() as $tableName => $table) {
				foreach ($table->getForeignKeys() as $fk) {
					
					// Get reference database
					if (isset($fk['ref_database'])) {
						$refDatabase = $table->getSchema()->getDatabase($fk['ref_database']);
					} else {
						$refDatabase = $table->getDatabase();
					}
					
					// Get reference table
					$refTable = $refDatabase->getTable($fk['ref_table']);
					
					// Get reference columns
					$refKey = array();
					foreach ($fk['ref_key'] as $columnName) {
						$refKey[] = $refTable->getColumn($columnName);
					}
					
					// Get reference "object name"
					if (isset($fk['ref_object_name'])) {
						$refObjectName = $fk['ref_object_name'];
					} else {
						$refObjectName = $refTable->getTableName();
					}
					
					// Get local columns
					$localKey = array();
					foreach ($fk['local_key'] as $columnName) {
						$localKey[] = $table->getColumn($columnName);
					}
					
					// Get local "object name"
					if (isset($fk['local_object_name'])) {
						$localObjectName = $fk['local_object_name'];
					} else {
						$localObjectName = $table->getTableName();
					}
					
					// If a table has an FK, two things happen:
					
					// 1. The local table can pull a "has one" relationship to the reference table
					
					$hasOne = new SchemaRelationship();
					$hasOne->setRefTable($refTable);
					$hasOne->setRefObjectName($refObjectName);
					$hasOne->setRefKey($refKey);
					$hasOne->setLocalTable($table);
					$hasOne->setLocalObjectName($localObjectName);
					$hasOne->setLocalKey($localKey);
					
					$table->addHasOneRelationship($hasOne);
					
					// 2. The reference table can pull a "has many" relationship to the local table
					
					$hasMany = new SchemaRelationship();
					$hasMany->setRefTable($table);
					$hasMany->setRefObjectName($localObjectName);
					$hasMany->setRefKey($localKey);
					$hasMany->setLocalTable($refTable);
					$hasMany->setLocalObjectName($refObjectName);
					$hasMany->setLocalKey($refKey);
					
					$refTable->addHasManyRelationship($hasMany);
				}
			}
		}
		
		// Convert some one-to-many relationships into many-to-many relationships
		foreach ($this->getDatabases() as $dbName => $database) {
			foreach ($database->getTables() as $tableName => $table) {
				// 3. A potential many-to-many can be found....
				foreach ($table->getHasManyRelationships() as $hasManyKey => $hasMany) {
					foreach ($hasMany->getRefTable()->getHasOneRelationships() as $hasOne) {
						if ($hasOne->getRefTable() != $table) {
							// TODO: Anthony: washere
							echo 'Found Many-to-Many relationship' . "\n";
							// $habtm = new SchemaRelationship();
							// $habtm->setJoinTable($hasMany->getRefTable());
							// $table->addHabtmRelationship($habtm);
							// $table->removeHasManyRelationship($hasManyKey);
						}
					}
				}
				
			}
		}
		
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