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
	
	/**
	 * Keep track of whether or not the base database drivers have been loaded.
	 * (we'll load them on-the-fly only if needed)
	 *
	 * @var string
	 **/
	protected $hasLoadedBaseDrivers = false;
	
	/**
	 * Whether or not to echo what's happening to the screen.
	 *
	 * @var boolean
	 **/
	protected $verbose = false;
	
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
			if ($this->config->shouldProcessDatabase($dbName)) {
				if ($this->verbose) {
					echo 'Scanning database `' . $dbName . "`\n";
				}
				$database = $server->loadDatabase($dbName);
				foreach ($database->getAvailableTableNames() as $tableName) {
					if ($this->config->shouldProcessTable($dbName, $tableName)) {
						if ($this->verbose) {
							echo "\tScanning table `" . $tableName . "`\n";
						}
						$table = $database->loadTable($tableName);
					} else {
						if ($this->verbose) {
							echo "\tSkipping table `" . $tableName . "`\n";
						}
					}
				}
			} else {
				if ($this->verbose) {
					echo 'Skipping database `' . $dbName . "`\n";
				}
			}
		}
		
		$this->setSchema($server);
	}
	
	/**
	 * Load database schema drivers for the specified path and driver prefix.
	 * 
	 * @param string $path - full path with trailing slash
	 * @param string $classPrefix
	 * @return void
	 **/
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
	
	/**
	 * Enable verbose mode
	 *
	 * @return void
	 * @see {@link $verbose}
	 **/
	public function enableVerbose() {
		$this->verbose = true;
	}
	
	/**
	 * Disable verbose mode
	 *
	 * @return void
	 * @see {@link $verbose}
	 **/
	public function disableVerbose() {
		$this->verbose = false;
	}

	/**
	 * Get the Schema / DriverServer object.
	 *
	 * @return Schema
	 **/
	public function getSchema() {
		return $this->schema;
	}
	
	/**
	 * Set the Schema / DriverServer object.
	 *
	 * @return void
	 **/
	public function setSchema(Schema $schema) {
		$this->schema = $schema;
	}
	
}

?>