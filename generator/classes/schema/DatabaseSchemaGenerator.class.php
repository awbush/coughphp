<?php

class DatabaseSchemaGenerator extends SchemaGenerator {
	
	/**
	 * Configuration object for this class
	 *
	 * @var DatabaseSchemaGeneratorConfig
	 **/
	protected $config = null;
	
	protected $databases = array();
	protected $schemas = array();
	
	// TODO: Split out into configuration options
	protected $idSuffix = '_id';
	
	
	public function __construct($config = array()) {
		$this->initConfig($config);
	}
	
	public function initConfig($config) {
		if ($config instanceof DatabaseSchemaGeneratorConfig) {
			$this->config = $config;
		} else if (is_array($config)) {
			$this->config = new DatabaseSchemaGeneratorConfig($config);
		} else {
			throw new Exception('First parameter must be an array or DatabaseSchemaGeneratorConfig object.');
		}
	}
	
	// /**
	//  * Load a database object to include in the schema.
	//  *
	//  * @return void
	//  * @author Anthony Bush
	//  **/
	// public function loadDatabase($db) {
	// 	$this->databases[$db->getDatabaseName()] = $db;
	// }
	
	public function loadServer() {
		$dsn = $this->config->getDsn();
		$serverClass = ucfirst(strtolower($dsn['driver'])) . 'Server';
		$server = new $serverClass($dsn);
		
		$dbNames = $server->getDatabaseNames();
		
		$this->databases = array();
		foreach ($dbNames as $dbName) {
			$this->loadDatabase($dbName);
		}
		
		$server->loadDatabases();
	}
	
	/**
	 * Traverse all the databases, tables, and columns to build a schema, which
	 * basically contains the same information with the addition of
	 * relationships between tables.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function generateSchema() {
		$this->loadServer();
		
		// Loop through the databases, using the cough naming conventions (or configuration?) to link relationships.
		
		$this->schemas = array();
		
		foreach ($this->databases as $dbName => $db) {
			$this->schemas[$dbName] = array();
			foreach ($db->getTables() as $tableName => $table) {
				$this->schemas[$dbName][$tableName]['primary_key'] = $table->getPrimaryKey();
				$this->schemas[$dbName][$tableName]['columns'] = $table->getColumns();
				
				// get belongs to one relationships in the current database/schema
				$primaryKey = $table->getPrimaryKey();
				foreach ($table->getColumns() as $columnName => $column) {
					if ($this->isForeignKey($columnName)) {
						// If we have a multi-key PK (or no PK), search for related table.
						if (count($primaryKey) != 1 || !isset($primaryKey[$columnName])) {

						}

						foreach ($db->getTables() as $relatedTableName => $relatedTable) {
							if ($relatedTableName != $tableName) {

							}
						}
					}
				}
				$this->schemas[$dbName][$tableName];
				$this->schemas[$dbName][$tableName]['belongs_to_one'] = $table->getColumns();

				// TODO: get belongs to one relationships for other databases/schemads
								
				
			}
		}
		
		echo '<pre>';
		print_r($this->schemas);
		echo '</pre>';
		return $this->schemas;
	}
	
	/**
	 * Returns whether or not the given dbColumnName is is a foreign key.
	 *
	 * @return boolean true if given column name is a foreign key, false if not.
	 * @author Anthony Bush
	 **/
	protected function isForeignKey($dbColumnName) {
		return (substr($dbColumnName, -strlen($this->idSuffix)) == $this->idSuffix
			&& (strpos($dbColumnName, '2') === false));
	}
	
	
}

?>