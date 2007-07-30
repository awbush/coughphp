<?php
// include_once('load.inc.php');
// 
// $server = new MysqlServer('127.0.0.1', 'root', '4Ri&uqE6');
// $server->loadDatabase('mediapc');
// 
// $schemaGenerator = new CoughSchemaGenerator();
// $schemaGenerator->loadDatabase($server->getDatabase('mediapc'));
// $schemas  = $schemaGenerator->generateSchemas();


class CoughSchemaGenerator {
	
	protected $databases = array();
	protected $schemas = array();
	
	// TODO: Split out into configuration options
	protected $idSuffix = '_id';
	
	/**
	 * Construct a CoughSchemaGenerator with a CoughConfig object.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function __construct($config) {
		$this->config = $config;
	}
	
	/**
	 * Load a database object to include in the schema.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function loadDatabase($db) {
		$this->databases[$db->getDatabaseName()] = $db;
	}
	
	/**
	 * Traverse all the databases, tables, and columns to build a schema, which
	 * basically contains the same information with the addition of
	 * relationships between tables.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	public function generateSchemas() {
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