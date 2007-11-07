<?php

/**
 * Schema contains one or more databases, which each may have info about one or
 * more tables, which each may have info about one or more columns, which each
 * have info about whether it is a primary key, null is allowed, its type, etc.
 *
 * @package schema
 * @author Anthony Bush
 **/
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
					
					// Skip this fk if we've already linked it.
					if ($fk->isLinked()) {
						continue;
					}
					
					// Get reference database
					if ($fk->hasRefDatabaseName()) {
						$refDatabase = $table->getSchema()->getDatabase($fk->getRefDatabaseName());
					} else {
						$refDatabase = $table->getDatabase();
					}
					
					// Get reference table
					$refTable = $refDatabase->getTable($fk->getRefTableName());
					
					if (is_null($refTable)) {
						echo 'ERROR IN DATABASE SCHEMA: FK setup to non-existing table. Take a look at ' . $dbName . '.' . $tableName . "\n";
						die();
					}
					
					// Get reference columns
					$refKey = array();
					foreach ($fk->getRefKeyName() as $columnName) {
						$refKey[] = $refTable->getColumn($columnName);
					}
					
					// Get reference "object name"
					if ($fk->hasRefObjectName()) {
						$refObjectName = $fk->getRefObjectName();
					} else {
						$refObjectName = $refTable->getTableName();
					}
					
					// Get local columns
					$localKey = array();
					foreach ($fk->getLocalKeyName() as $columnName) {
						$localKey[] = $table->getColumn($columnName);
					}
					
					// Get local "object name"
					if ($fk->hasLocalObjectName()) {
						$localObjectName = $fk->getLocalObjectName();
					} else {
						$localObjectName = $table->getTableName();
					}
					
					// If a table has an FK, two things happen:
					
					// 1. The local table can pull a "has one" relationship to the reference table
					
					$hasOne = new SchemaRelationshipHasOne();
					$hasOne->setRefTable($refTable);
					$hasOne->setRefObjectName($refObjectName);
					$hasOne->setRefKey($refKey);
					$hasOne->setLocalTable($table);
					$hasOne->setLocalObjectName($localObjectName);
					$hasOne->setLocalKey($localKey);
					
					$table->addHasOneRelationship($hasOne);
					
					// 2. The reference table can pull a "has many" relationship to the local table
					
					$hasMany = new SchemaRelationshipHasMany();
					$hasMany->setRefTable($table);
					$hasMany->setRefObjectName($localObjectName);
					$hasMany->setRefKey($localKey);
					$hasMany->setLocalTable($refTable);
					$hasMany->setLocalObjectName($refObjectName);
					$hasMany->setLocalKey($refKey);
					
					$refTable->addHasManyRelationship($hasMany);
					
					// Link the has one and has many together
					$hasOne->setHasManyRelationship($hasMany);
					$hasMany->setHasOneRelationship($hasOne);
					
					// Set the FK as linked so we don't link it up again.
					$fk->setIsLinked(true);
				}
			}
		}
	}
	
	public function outputRelationshipCounts() {
		foreach ($this->getDatabases() as $database) {
			foreach ($database->getTables() as $table) {
				echo 'Table ' . $table->getTableName() . ' has ' . "\n";
				echo "\t" . count($table->getHasOneRelationships()) . ' one-to-one relationships.' . "\n";
				echo "\t" . count($table->getHasManyRelationships()) . ' one-to-many relationships.' . "\n";
				// echo "\t" . count($table->getHabtmRelationships()) . ' many-to-many relationships.' . "\n";
			}
		}
	}
	
	public function getNumberOfHasOneRelationships() {
		$count = 0;
		foreach ($this->getDatabases() as $database) {
			foreach ($database->getTables() as $table) {
				$count += count($table->getHasOneRelationships());
			}
		}
		return $count;
	}
	
	public function getNumberOfHasManyRelationships() {
		$count = 0;
		foreach ($this->getDatabases() as $database) {
			foreach ($database->getTables() as $table) {
				$count += count($table->getHasManyRelationships());
			}
		}
		return $count;
	}
	
}

?>
