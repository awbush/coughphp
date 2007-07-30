<?php

class CoughConfig {
	protected $config = array();
	
	public function __construct($config) {
		$this->initConfig();
		$this->mergeIntoConfig($config);
	}
	
	/**
	 * Initializes config to the defaults
	 *
	 * @return void
	 * @author Anthony Bush
	 * @since 2007-06-15
	 **/
	protected function initConfig() {
		$this->config = array(
			'phpDoc' => array(
				'author' => 'CoughGenerator',
				'package' => 'default',
				'copyright' => '',
			),
			'paths' => array(
				'generated_classes' => 'generated/',
				'starter_classes' => 'generated/',
				'file_suffix' => '.class.php',
			),
			'class_names' => array(
				'prefix' => '',
				'base_object_suffix' => '_Generated',
				'base_collection_suffix' => '_Collection_Generated',
				'starter_object_suffix' => '',
				'starter_collection_suffix' => '_Collection',
			),
			'table_settings' => array(
				// This match setting is so the database scanner can resolve relationships better, e.g. know that when it sees "ticket_id" that a "wfl_ticket" table is an acceptable match.
				'match_table_name_prefixes' => array(), // e.g. array('cust_', 'wfl_', 'baof_'),
				// Additionally, you can strip table prefixes from the generated class names (note that you might run into naming conflicts though.)
				'strip_table_name_prefixes' => array(), // e.g. array('cust_', 'wfl_', 'baof_'),
				// You can ignore tables all together, too:
				'ignore_tables_matching_regex' => '/(_bak$)|(^bak_)|(^temp_)/',
			),
			'field_settings' => array(
				'id_suffix' => '_id',
				'retired_column' => 'is_retired',
				'is_retired_value' => '1',
				'is_not_retired_value' => '0', // TODO: deprecate this. Have the code use != is_retired_value
			),

			// All databases will be scanned unless specified in the 'databases' parameter.
			'dsn' => array(
				'host' => 'localhost',
				'user' => 'nobody',
				'pass' => '',
				'port' => 3306,
			),
		);
	}
	
	/**
	 * Merges the given config into the current config, overriding any of the
	 * current config values to new ones.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function mergeIntoConfig($config) {
		// Only override the inner most pieces (currently just the 2nd level)
		foreach ($config as $key1 => $value1) {
			foreach ($value1 as $key2 => $value2) {
				$this->config[$key1][$key2] = $value2;
			}
		}
	}
	
	/**
	 * Internal method to get a configuration setting. If a database name is given,
	 * it will check if the requested config has been overridden for that database
	 * and return that config instead of the globally set one. If a database name
	 * AND a table name is given, Then it will check if the table config has been
	 * overridden first, then the database config and then the globally set config.
	 * The first config it finds will be the one returned.
	 * 
	 * @param $key - separate config sections by a slash '/'. For example, if you
	 * want the "match_table_name_prefixes" config in the "table_settings" section,
	 * $key should be "table_settings/match_table_name_prefixes"
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function getConfigValue($configKey, $dbName = null, $tableName = null) {
		$keys = explode('/', $configKey);
		$value = null;
		
		if (!is_null($dbName)) {
			if (!is_null($tableName)) {
				// Look for table config first
				$finalKey = array_merge(array('dsn', 'databases', $dbName, 'tables', $tableName), $configKey);
				$value = $this->array_value_from_multi_key($this->config, $finalKey);
			}
			
			// Look for databaes config next
			if (is_null($value)) {
				$finalKey = array_merge(array('dsn', 'databases', $dbName), $configKey);
				$value = $this->array_value_from_multi_key($this->config, $finalKey);
			}
		}
		
		// Look at global config
		if (is_null($value)) {
			$value = $this->array_value_from_multi_key($this->config, $configKey);
		}
		
		// No config found, return null
		return $value;
	}
	
	/**
	 * Takes a source array and an array of keys and attempts to traverse
	 * the source array using the keys. It's a way to dynamically retrieve
	 * a value deep in an array using keys that are not none until runtime.
	 * 
	 * For example:
	 * 
	 *     $source = array('one' => array('two' => 'three'));
	 *     $keys = array('one', 'two');
	 *     echo array_value_from_multi_key($source, $keys);
	 * 
	 * outputs "three"
	 * 
	 * @return mixed - null if any of the keys are found, otherwise the value deep in the source array.
	 * @author Anthony Bush
	 **/
	protected function array_value_from_multi_key(&$value, $keys) {
		foreach ($keys as $key) {
			if (is_array($value) && isset($value[$key])) {
				// So far so good
				$value =& $value[$key];
			} else {
				// Uh oh, key not found in array
				return null;
			}
		}
		// All keys where found. What's left is the desired value
		return $value;
	}
	
	public function shouldGenerateForDatabase($db) {
		// If custom database config is specified, then only return true if the given database object is in there.
		if (isset($this->config['dsn']['databases'])) {
			if (!isset($this->config['dsn']['databases'][$db->getDatabaseName()])) {
				return false;
			}
		}
		return true;
	}
	
	public function shouldGenerateForTable($table) {
		// If custom database config is specified AND custom table config is specified, then only return true if the given database object is in there.
		if (isset($this->config['dsn']['databases'])) {
			$dbName = $table->getDatabase()->getDatabaseName();
			if (isset($this->config['dsn']['databases'][$dbName])) {
				if (isset($this->config['dsn']['databases'][$dbName]['tables'])) {
					if (!isset($this->config['dsn']['databases'][$dbName]['tables'][$table->getTableName()])) {
						return false;
					}
				}
			}
		}
		return true;
	}
	
	public function getDsn() {
		return $this->config['dsn'];
	}
	
}

?>