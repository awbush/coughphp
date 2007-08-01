<?php

abstract class CoughConfig {
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
	abstract protected function initConfig();
	
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
				$finalKey = array_merge(array('databases', $dbName, 'tables', $tableName), $configKey);
				$value = $this->getArrayValueFromMultiKey($this->config, $finalKey);
			}
			
			// Look for databaes config next
			if (is_null($value)) {
				$finalKey = array_merge(array('databases', $dbName), $configKey);
				$value = $this->getArrayValueFromMultiKey($this->config, $finalKey);
			}
		}
		
		// Look at global config
		if (is_null($value)) {
			$value = $this->getArrayValueFromMultiKey($this->config, $configKey);
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
	 *     echo getArrayValueFromMultiKey($source, $keys);
	 * 
	 * outputs "three"
	 * 
	 * @return mixed - null if any of the keys are found, otherwise the value deep in the source array.
	 * @author Anthony Bush
	 **/
	protected function getArrayValueFromMultiKey(&$value, $keys) {
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
	
	/**
	 * Returns whether not the given database is meant to be processed.
	 * 
	 * @param AbstractDatabase $db
	 * @return boolean
	 * @author Anthony Bush
	 * @todo Should we move this into one of the concrete config classes or rename the function?
	 **/
	public function shouldProcessDatabase($db) {
		// If custom database config is specified, then only return true if the given database object is in there.
		if (isset($this->config['databases'])) {
			if (!isset($this->config['databases'][$db->getDatabaseName()])) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Returns whether not the given table is meant to be processed.
	 * 
	 * @param AbstractTable $table
	 * @return boolean
	 * @author Anthony Bush
	 * @todo Should we move this into one of the concrete config classes or rename the function?
	 **/
	public function shouldProcessTable($table) {
		// If custom database config is specified AND custom table config is specified, then only return true if the given database object is in there.
		if (isset($this->config['databases'])) {
			$dbName = $table->getDatabase()->getDatabaseName();
			if (isset($this->config['databases'][$dbName])) {
				if (isset($this->config['databases'][$dbName]['tables'])) {
					if (!isset($this->config['databases'][$dbName]['tables'][$table->getTableName()])) {
						return false;
					}
				}
			} else {
				return false; // databases array specified, but the database for this table is not in it.
			}
		}
		return true;
	}
	
}

?>