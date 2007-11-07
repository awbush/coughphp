<?php

/**
 * Takes XML config info and generates a schema (a collection of databases
 * each containing a collection of tables each containing a collection of columns)
 *
 * @package schema_generator
 * @author Anthony Bush
 * @todo Write this class...
 **/
class XmlSchemaGenerator extends SchemaGenerator {
	
	/**
	 * Configuration object for this class
	 *
	 * @var XmlSchemaGeneratorConfig
	 **/
	protected $config = null;
	
	/**
	 * Schema object (mostly likely an instanceof Schema)
	 *
	 * @var Schema
	 **/
	protected $schema = null;
	
	/**
	 * Construct with optional configuration parameters.
	 * 
	 * @param mixed $config - either an array of configuration variables or a pre-constructed XmlSchemaGeneratorConfig object.
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
		if ($config instanceof XmlSchemaGeneratorConfig) {
			$this->config = $config;
		} else if (is_array($config)) {
			$this->config = new XmlSchemaGeneratorConfig($config);
		} else {
			throw new Exception('First parameter must be an array or XmlSchemaGeneratorConfig object.');
		}
	}
	
	/**
	 * Loads the schema into memory according to the config (e.g. only includes
	 * databases and tables the config allows).
	 *
	 * @return void
	 * @todo open an XML file and generate schema in memory
	 **/
	public function loadSchema() {
		
	}
	
	/**
	 * Generate/load the schema and return it.
	 * 
	 * @return Schema
	 * @author Anthony Bush
	 **/
	public function generateSchema() {
		$this->loadSchema();
		return $this->getSchema();
	}
	
}

?>