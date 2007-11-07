<?php

/**
 * Takes a schema (and config) and runs through the schema in an effort to
 * detect foreign keys that may have been missed (or simply weren't specified
 * at the database level).
 *
 * @package schema
 * @author Anthony Bush
 **/
class SchemaManipulator {
	
	/**
	 * Configuration object for this class
	 *
	 * @var DatabaseSchemaGeneratorConfig
	 **/
	protected $config = null;
	
	/**
	 * Whether or not to echo what's happening to the screen.
	 *
	 * @var boolean
	 **/
	protected $verbose = false;
	protected $debug = false;
	
	/**
	 * A reference to the schema to manipulate
	 *
	 * @var Schema
	 **/
	protected $schema = null;
	
	/**
	 * Construct with optional configuration parameters.
	 * 
	 * @param mixed $config - either an array of configuration variables or a pre-constructed DatabaseSchemaGeneratorConfig object.
	 * @return void
	 **/
	public function __construct($config = array()) {
		$this->initConfig($config);
	}
	
	/**
	 * Initialize the configuration object given an array or pre-constructed
	 * configuration object.
	 *
	 * @return void
	 * @throws Exception
	 **/
	public function initConfig($config) {
		if ($config instanceof DatabaseSchemaGeneratorConfig) {
			$this->config = $config;
		} else if (is_array($config)) {
			$this->config = new DatabaseSchemaGeneratorConfig($config);
		} else {
			throw new Exception('First parameter must be an array or DatabaseSchemaGeneratorConfig object.');
		}
	}
	
	/**
	 * Enable verbose mode
	 *
	 * @return void
	 * @see $verbose
	 **/
	public function enableVerbose() {
		$this->verbose = true;
	}
	
	/**
	 * Disable verbose mode
	 *
	 * @return void
	 * @see $verbose
	 **/
	public function disableVerbose() {
		$this->verbose = false;
	}
	
	/**
	 * Scans the schema and manipulates it by detecting FKs adding them to
	 * tables.
	 * 
	 * It uses the id_to_table_regex config setting to find columns that might
	 * be FKs, and uses match_table_name_prefixes to assist in locating a table
	 * that matches the FK column name.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function manipulateSchema($schema)
	{
		$this->schema = $schema;
		
		// add any missed FKs that we can detect via naming A.S. conventions
		foreach ($schema->getDatabases() as $localDatabaseName => $database)
		{
			foreach ($database->getTables() as $localTableName => $table)
			{
				// Get the per database/table setting for id_to_table_regex
				$idToTableRegexes = $this->config->getIdToTableRegex($table);
				
				// Loop through the table's columns and setup an FK for any ID matches.
				foreach ($table->getColumns() as $column)
				{
					// Skip primary keys b/c we are looking for FKs
					// TODO: can you have both PK and FK? if so, we need to allow this and check for it on the other end to make sure we don't link the table to itself.
					if ($column->isPrimaryKey()) {
						continue;
					}
					
					// If the ID matches, scan the table suggested by the parsed value
					// from the id_to_table_regex and the other config settings
					$matches = array();
					
					foreach ($idToTableRegexes as $idToTableRegex) {
						if (!preg_match($idToTableRegex, $column->getColumnName(), $matches)) {
							if ($this->debug) {
								echo "\tit_to_table_regex '$idToTableRegex' did not match " . $column->getColumnName() . "\n";
							}
							continue;
						}
						if ($this->debug) {
							echo "\tit_to_table_regex '$idToTableRegex' matched " . $column->getColumnName() . "\n";
						}
						
						if (isset($matches[1]))
						{
							$refTable = $this->findTable($matches[1], $table);

							// Add the foreign key
							if (!is_null($refTable) && count($refTable->getPrimaryKey()) == 1)
							{
								$refDatabaseName = $refTable->getDatabase()->getDatabaseName();
								
								$fk = new SchemaForeignKey();
								$fk->setLocalDatabaseName($localDatabaseName);
								$fk->setLocalTableName($localTableName);
								$fk->setLocalKeyName(array($column->getColumnName()));
								$fk->setRefDatabaseName($refDatabaseName);
								$fk->setRefTableName($refTable->getTableName());
								
								// Downgrade the ref primary key to an array of column names (no references to the objects)
								$refKeyName = array();
								foreach ($refTable->getPrimaryKey() as $pkColumn) {
									$refKeyName[] = $pkColumn->getColumnName();
								}
								
								$fk->setRefKeyName($refKeyName);
								
								if ($this->verbose) {
									echo 'Detected FK by name';
									if ($table->hasForeignKey($fk)) {
										echo ' (which is already set up)';
									}
									$dbName = $table->getDatabase()->getDatabaseName();
									$refDbName = $refTable->getDatabase()->getDatabaseName();
									echo ': ' . $dbName . '.' . $table->getTableName() . ' (' . $column->getColumnName()
										. ') => ' . $refDbName . '.' . $refTable->getTableName() . ' (' . implode(',', $refKeyName) . ')' . "\n";
								}
								
								$table->addForeignKey($fk);
								
								// Stop looping through idToTableRegexes because we found a match.
								break;
							}
						}
						
					}
					
				}
			}
		}
		
		// (re)-link relationships
		$schema->linkRelationships();
	}
	
	/**
	 * Scans the schema for the specified table name (or prefix + table name),
	 * starting with the given database and only scanning other databases if
	 * the table is not found there.
	 *
	 * @return mixed - SchemaTable if found, null if not
	 * @author Anthony Bush
	 **/
	protected function findTable($tableNameMatch, SchemaTable $sourceTable)
	{
		$firstDatabase = $sourceTable->getDatabase();
		$table = $this->findTableInDatabase($tableNameMatch, $firstDatabase);
		
		if (is_null($table)) {
			// Try again for every other database
			$firstDatabaseName = $firstDatabase->getDatabaseName();
			foreach ($this->schema->getDatabases() as $dbName => $database) {
				if ($dbName == $firstDatabaseName || !$this->config->shouldScanForJoin($sourceTable, $database)) {
					continue;
				}
				$table = $this->findTableInDatabase($tableNameMatch, $database);
				if (!is_null($table)) {
					break;
				}
			}
			
		}
		
		return $table;
	}
	
	/**
	 * Scans the specified database for the specified table name (using the
	 * match_table_name_prefixes config setting to scan for more table names
	 * in the event the given one is not found.)
	 * 
	 * This is support method for {@link findTable()}.
	 *
	 * @return mixed - SchemaTable if found, null if not.
	 * @author Anthony Bush
	 **/
	protected function findTableInDatabase($tableNameMatch, SchemaDatabase $database)
	{
		$tableNamePrefixes = $this->config->getTableNamePrefixes($database);
		
		$tableNames = array($tableNameMatch);
		if (!is_null($tableNamePrefixes)) {
			foreach ($tableNamePrefixes as $prefix) {
				$tableNames[] = $prefix . $tableNameMatch;
			}
		}
		
		$table = null;
		foreach ($tableNames as $tableName) {
			$table = $database->getTable($tableName);
			if (!is_null($table)) {
				break;
			}
		}
		
		return $table;
	}
	
}

?>
