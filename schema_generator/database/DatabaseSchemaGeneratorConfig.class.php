<?php

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
			
			// You can override table_settings only on a per-database level and not a per-table level (b/c it doesn't make sense to)
			'table_settings' => array(
				// This match setting is so the database scanner can resolve relationships better, e.g. know that when it sees "ticket_id" that a "wfl_ticket" table is an acceptable match.
				'match_table_name_prefixes' => array(), // Example: array('cust_', 'wfl_', 'baof_'),
				// You can ignore tables all together, too:
				'ignore_tables_matching_regex' => '/(_bak$)|(^bak_)|(^temp_)/',
			),

			'field_settings' => array(
				// In case of non FK detection, you can have the Database Schema Generator check for ID columns matching this regex.
				// This is useful, for example, when no FK relationships set up)
				'id_to_table_regex' => '/^(.*)_id$/',
			),

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
	 * Returns whether not the given table name (for the given database name)
	 * should be processed.
	 * 
	 * @param string $tableName
	 * @return boolean
	 * @author Anthony Bush
	 * @todo Should we move this into one of the concrete config classes or rename the function?
	 **/
	public function shouldProcessTable($dbName, $tableName) {
		if (!parent::shouldProcessTable($dbName, $tableName)) {
			return false;
		}
		
		// Also check that the table is not set to be ignored via the regex.
		$ignoreRegex = $this->getConfigValue('table_settings/ignore_tables_matching_regex', $dbName);
		if (preg_match($ignoreRegex, $tableName)) {
			return false;
		}
		
		return true;
	}
	
	
}

?>