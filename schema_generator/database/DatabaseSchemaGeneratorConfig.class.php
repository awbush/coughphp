<?php

/**
 * Config class for DatabaseSchemaGenerator.
 * 
 * It abstracts the implementation of configuration specification, as well
 * as makes it easy for the DatabaseSchemaGenerator to perform different actions
 * based on the user's configuration.
 *
 * @package schema_generator
 * @author Anthony Bush
 **/
class DatabaseSchemaGeneratorConfig extends CoughConfig {
	
	public static function constructFromFile($filePath) {
		include($filePath);
		return new DatabaseSchemaGeneratorConfig($config);
	}
	
	protected function initConfig() {
		$this->config = array(
			// REQUIRED CONFIG

			// All databases will be scanned unless specified in the 'databases' parameter in the OPTIONAL CONFIG SECTION.
			'dsn' => array(
				'host' => 'localhost',
				'user' => 'nobody',
				'pass' => '',
				'port' => 3306,
				'driver' => 'mysql'
			),

			// OPTIONAL ADDITIONAL CONFIG
			
			'database_settings' => array(
				'include_databases_matching_regex' => '/.*/',
				'exclude_databases_matching_regex' => '/(_bak$)|(^bak_)|(^temp_)/',
			),
			
			// You can override table_settings only on a per-database level and not a per-table level (b/c it doesn't make sense to)
			'table_settings' => array(
				// This match setting is so the database scanner can resolve
				// relationships better, e.g. know that when it sees "ticket_id"
				// that a "wfl_ticket" table is an acceptable match.
				'match_table_name_prefixes' => array(), // Example: array('cust_', 'wfl_', 'baof_'),
				
				'include_tables_matching_regex' => '/.*/',
				'exclude_tables_matching_regex' => '/(_bak$)|(^bak_)|(^temp_)/',
			),

			'field_settings' => array(
				// In case of non FK detection, you can have the Database Schema Generator check for ID columns matching this regex.
				// This is useful, for example, when no FK relationships set up)
				'id_to_table_regex' => '/^(.*)_id$/',
			),
			
			'acceptable_join_databases' => 'all',

			// Now, we can override the global config on a per database level.
			// 'databases' => array(
			// 	'customer' => array(
			// 		'table_settings' => array(
			// 			'match_table_name_prefixes' => array('cust_'),
			// 		),
			// 
			// 		// Furthermore, we can override the table level settings
			// 		'tables' => array(
			// 			'table_name' => array(
			// 				'field_settings' => array(
			// 					'id_to_table_regex' => '/^(.*)_id$/',
			// 				),
			// 				// override what Cough will treat as PK -- useful for getting Cough
			// 				// to generate VIEWs or any other table that doesn't have a PK on it.
			// 				'primary_key' => array('column1', 'column2', etc...)
			// 			),
			// 		),
			// 	),
			// ),
		);
	}
	
	public function getDsn() {
		return $this->config['dsn'];
	}
	
	/**
	 * Returns an array of regexes to match against in order to determine if a
	 * field can be mapped to a table name, in order of precedence.
	 *
	 * @return array
	 * @author Anthony Bush
	 **/
	public function getIdToTableRegex(SchemaTable $table) {
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		$regexes = $this->getConfigValue('field_settings/id_to_table_regex', $dbName, $tableName);
		if (!is_array($regexes)) {
			return array($regexes);
		} else {
			return $regexes;
		}
	}
	
	public function getTableNamePrefixes(SchemaDatabase $database) {
		$dbName = $database->getDatabaseName();
		return $this->getConfigValue('table_settings/match_table_name_prefixes', $dbName);
	}
	
	/**
	 * Returns whether not the given database name should be processed.
	 * 
	 * @param string $dbName
	 * @return boolean
	 * @author Anthony Bush
	 **/
	public function shouldProcessDatabase($dbName) {
		
		// Check that the database should be included.
		$includeRegex = $this->getConfigValue('database_settings/include_databases_matching_regex', $dbName);
		if (!preg_match($includeRegex, $dbName)) {
			return false;
		}
		
		// Check that the database should not be excluded.
		$excludeRegex = $this->getConfigValue('database_settings/exclude_databases_matching_regex', $dbName);
		if (preg_match($excludeRegex, $dbName)) {
			return false;
		}
		return true;
	}
	
	/**
	 * Returns whether not the given table name (for the given database name)
	 * should be processed.
	 * 
	 * @param string $tableName
	 * @return boolean
	 * @author Anthony Bush
	 **/
	public function shouldProcessTable($dbName, $tableName) {
		
		// Check that the table should be included.
		$includeRegex = $this->getConfigValue('table_settings/include_tables_matching_regex', $dbName);
		if (!preg_match($includeRegex, $tableName)) {
			return false;
		}
		
		// Check that the table should not be excluded.
		$excludeRegex = $this->getConfigValue('table_settings/exclude_tables_matching_regex', $dbName);
		if (preg_match($excludeRegex, $tableName)) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Returns whether or not a the $sourceTable cares about the $refDatabase when looking
	 * for joins.
	 * 
	 * @param SchemaTable $sourceTable - the source table that is trying to link up related joins
	 * @param SchemaDatabase $refDatabase - the dest database to scan if shouldScanForJoin() returns true
	 * @return boolean
	 * @author Anthony Bush
	 * @since 2007-11-06
	 **/
	public function shouldScanForJoin(SchemaTable $sourceTable, SchemaDatabase $refDatabase) {
		$dbName = $sourceTable->getDatabase()->getDatabaseName();
		$tableName = $sourceTable->getTableName();
		$option = $this->getConfigValue('acceptable_join_databases', $dbName, $tableName);
		if (is_array($option)) {
			// yes, but only for databases in the array.
			$refDbName = $refDatabase->getDatabaseName();
			if (in_array($refDbName, $option)) {
				return true;
			}
		} else if ($option == 'all') {
			return true;
		}
		return false;
	}
	
	public function getPrimaryKeyOverride($dbName, $tableName)
	{
		return $this->getConfigValue('primary_key', $dbName, $tableName, CoughConfig::SCOPE_TABLE);
	}
	
}

?>
