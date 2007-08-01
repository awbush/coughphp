<?php

class CoughGenerator {
	
	/**
	 * Configuration object for this class
	 *
	 * @var DatabaseSchemaGeneratorConfig
	 **/
	protected $config = null;
	
	protected $generatedClasses = array();
	
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
		if ($config instanceof CoughGeneratorConfig) {
			$this->config = $config;
		} else if (is_array($config)) {
			$this->config = new CoughGeneratorConfig($config);
		} else {
			throw new Exception('First parameter must be an array or CoughGeneratorConfig object.');
		}
	}
	
	/**
	 * Generates Cough classes from a Schema or SchemaTable object. (It just
	 * calls the appropriate generation method based on the object type.)
	 * 
	 * @param mixed $schema - Schema or SchemaTable
	 * @return array of CoughClass objects
	 * @author Anthony Bush
	 **/
	public function generateCoughClasses($schema) {
		if ($schema instanceof Schema) {
			$this->generateCoughClassesFromSchema($schema);
		} else if ($schema instanceof SchemaTable) {
			$this->generateCoughClassesFromSchemaTable($schema);
		} else {
			throw new Exception('Unknown object type ' . get_class($schema) . '. Use Schema or SchemaTable objects.');
		}
		
		return $this->getGeneratedClasses();
	}
	
	/**
	 * Generates Cough classes from a Schema object.
	 * 
	 * @param Schema $schema
	 * @return void
	 * @author Anthony Bush
	 **/
	public function generateCoughClassesFromSchema(Schema $schema) {
		foreach ($schema->getDatabases() as $database) {
			foreach ($database->getTables() as $table) {
				$this->generateCoughClassesFromSchemaTable($table);
			}
		}
	}
	
	/**
	 * Generates Cough classes from a SchemaTable object.
	 * 
	 * @param SchemaTable $table
	 * @return void
	 * @author Anthony Bush
	 **/
	public function generateCoughClassesFromSchemaTable(SchemaTable $table) {
		// Generate the class
		// $className = $this->config->getClassName($table->getTableName(), $isStarter = false, $isCollection = false);
		// $tableNameCamel = $this->config->getCamelCase($table->getTableName());
		
		$className = 'Test';
		$contents = '<?php class ' . $className . ' extends CoughObject {} ?>';
		$coughClass = new CoughClass($className, $contents);
		
		// Add the class
		$this->addGeneratedClass($coughClass);
	}
	
	
	
	public function getGeneratedClasses() {
		return $this->generatedClasses;
	}
	
	public function addGeneratedClass($coughClass) {
		$this->generatedClasses[$coughClass->getClassName()] = $coughClass;
	}
}

?>