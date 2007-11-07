<?php

/**
 * Base config class for other config classes to extend.
 *
 * @package cough_generator
 * @author Anthony Bush
 **/
abstract class CoughConfig {
	const SCOPE_GLOBAL = 1;
	const SCOPE_DATABASE = 2;
	const SCOPE_TABLE = 3;
	
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
			if (is_array($value1)) {
				foreach ($value1 as $key2 => $value2) {
					$this->config[$key1][$key2] = $value2;
				}
			} else {
				$this->config[$key1] = $value1;
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
	 * @param $configKey - separate config sections by a slash '/'. For example, if you
	 * want the "match_table_name_prefixes" config in the "table_settings" section,
	 * $configKey should be "table_settings/match_table_name_prefixes"
	 *
	 * @return mixed - config value or null if not found.
	 * @author Anthony Bush
	 **/
	protected function getConfigValue($configKey, $dbName = null, $tableName = null, $scope = self::SCOPE_GLOBAL) {
		$keys = explode('/', $configKey);
		$value = null;
		
		if (!is_null($dbName)) {
			if (!is_null($tableName)) {
				// Look for table config first (in GLOBAL, DATABASE, or TABLE scopes)
				$finalKey = array_merge(array('databases', $dbName, 'tables', $tableName), $keys);
				$value = $this->getArrayValueFromMultiKey($this->config, $finalKey);
			}
			
			// Look for databaes config next (in GLOBAL and DATABASE scopes only)
			if (is_null($value) && $scope != self::SCOPE_TABLE) {
				$finalKey = array_merge(array('databases', $dbName), $keys);
				$value = $this->getArrayValueFromMultiKey($this->config, $finalKey);
			}
		}
		
		// Look at global config (in GLOBAL scope only)
		if (is_null($value) && $scope == self::SCOPE_GLOBAL) {
			$value = $this->getArrayValueFromMultiKey($this->config, $keys);
		}
		
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
	
}

?>
