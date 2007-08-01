<?php

/**
 * Takes database config info and generates a schema (a collection of databases
 * each containing a collection of tables each containing a collection of columns)
 *
 * @package CoughPHP
 * @author Anthony Bush
 **/
class DatabaseSchemaGenerator extends SchemaGenerator {
	
	/**
	 * Configuration object for this class
	 *
	 * @var DatabaseSchemaGeneratorConfig
	 **/
	protected $config = null;
	
	/**
	 * Schema object (mostly likely an instanceof Schema)
	 *
	 * @var Schema
	 **/
	protected $schema = null;
	
	protected $hasLoadedBaseDrivers = false;
	
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
	 * Loads the schema into memory according to the config (e.g. only includes
	 * databases and tables the config allows).
	 * 
	 * This function is mostly the first pass in schema generator. A good second
	 * pass would be to attempt to link any relationships not possible through
	 * FK detection. See {@link linkRelationships()}.
	 *
	 * @return void
	 **/
	public function loadSchema() {
		$dsn = $this->config->getDsn();
		
		// Load the driver-specific classes
		$driver = ucfirst(strtolower($dsn['driver']));
		$this->loadDrivers(dirname(__FILE__) . '/drivers/mysql/', $driver);
		
		// Construct the server/schema class and start loading databases according to the configuration options.
		$serverClass = $driver . 'Server';
		$server = new $serverClass($dsn);
		
		$dbNames = $server->getAvailableDatabaseNames();
		foreach ($dbNames as $dbName) {
			$database = $server->loadDatabase($dbName);
			foreach ($database->getAvailableTableNames() as $tableName) {
				$table = $database->loadTable($tableName);
			}
		}
		
		$this->setSchema($server);
	}
	
	public function getSchema() {
		return $this->schema;
	}
	
	public function setSchema($schema) {
		$this->schema = $schema;
	}
	
	public function loadDrivers($path, $classPrefix) {
		if (!$this->hasLoadedBaseDrivers) {
			// Load the base driver classes/interfaces
			$this->hasLoadedBaseDrivers = true;
			$this->loadDrivers(dirname(__FILE__) . '/drivers/base/', 'Driver');
		}
		$prefix = $path . $classPrefix;
		include_once($prefix . 'Column.class.php');
		include_once($prefix . 'Database.class.php');
		include_once($prefix . 'Server.class.php');
		include_once($prefix . 'Table.class.php');
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
		$this->loadSchema();
		$this->linkRelationships();
		return $this->getSchema();
	}
	
	public function linkRelationships() {
		// Loop through the databases, using the cough naming conventions (or configuration?) to link relationships.
		
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