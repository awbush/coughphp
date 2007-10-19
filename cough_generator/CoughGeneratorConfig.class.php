<?php

class CoughGeneratorConfig extends CoughConfig {
	
	protected function initConfig() {
		$generated = dirname(dirname(__FILE__)) . '/generated/';

		$this->config = array(
			'phpDoc' => array(
				'author' => 'CoughGenerator',
				'package' => 'default',
				'copyright' => '',
			),
			'paths' => array(
				'generated_classes' => $generated . 'generated_classes/',
				'starter_classes' => $generated . 'starter_classes/',
				'file_suffix' => '.class.php',
			),
			'class_names' => array(
				// You can add prefixes to class names that are generated
				'prefix' => '',
				// Additionally, you can strip table prefixes from the generated class names (note that you might run into naming conflicts though.)
				'strip_table_name_prefixes' => array(), // Example: array('cust_', 'wfl_', 'baof_'),
				// Suffixes...
				'base_object_suffix' => '_Generated',
				'base_collection_suffix' => '_Collection_Generated',
				'starter_object_suffix' => '',
				'starter_collection_suffix' => '_Collection',
				// You can use your on "AppCoughObject" class here instead, if you want.
				'object_extension_class_name' => 'CoughObject',
				'collection_extension_class_name' => 'CoughCollection',
			),
			'field_settings' => array(
				'id_regex' => '/^(.*)_id$/',
				'delete_flag_column' => 'is_retired',
				'delete_flag_value' => '1',
			),

			// Now, we can override the global config on a per database level.
			// 'databases' => array(
			// 	'user' => array(
			// 		'class_names' => array(
			// 			'prefix' => 'usr_'
			// 		),
			// 		'table_settings' => array(
			// 			'strip_table_name_prefixes' => array('wfl_', 'baof_'),
			// 		),
			// 
			// 		// Furthermore, we can override the table level settings
			// 		'tables' => array(
			// 			'table_name' => array(
			// 				'field_settings' => array(
			// 					'id_regex' => '/^(.*)_id$/',
			// 					'delete_flag_column' => 'is_retired',
			// 					'delete_flag_value' => '1',
			// 				),
			// 			),
			// 		),
			// 	),
			// ),
		);
	}
	
	// public function getTableConfigValue($configKey, SchemaTable $table) {
	// 	return $this->getConfigValue($configKey, $table->getDatabase()->getDatabaseName(), $table->getTableName(), CoughConfig::SCOPE_TABLE);
	// }
	
	public function getClassName(SchemaTable $table) {
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		$className = $this->getConfigValue('class_name', $dbName, $tableName, CoughConfig::SCOPE_TABLE);
		
		if (is_null($className)) {
			$prefixes = $this->getConfigValue('class_names/strip_table_name_prefixes', $dbName, $tableName);
			
			// If a prefix exists in the table name, remove it
			$tableNameWithoutPrefix = $tableName;
			foreach ($prefixes as $prefix) {
				if (substr($tableName, 0, strlen($prefix)) == $prefix) {
					$tableNameWithoutPrefix = substr($tableName, strlen($prefix) - 1);
					break;
				}
			}
			
			$className = $this->getTitleCase($tableNameWithoutPrefix);
		}
		
		return $className;
	}
	
	public function getClassFileName(CoughClass $class) {
		$dbName = $class->getDatabaseName();
		$tableName = $class->getTableName();
		
		// Get file path
		if ($class->isStarterClass()) {
			$filePath = $this->getConfigValue('paths/starter_classes', $dbName, $tableName);
		} else {
			$filePath = $this->getConfigValue('paths/generated_classes', $dbName, $tableName);
		}
		
		// Get file suffix
		$fileSuffix = $this->getConfigValue('paths/file_suffix', $dbName, $tableName);
		
		// Put it all together
		return $filePath . $class->getClassName() . $fileSuffix;
	}
	
	public function getStarterObjectClassName(SchemaTable $table) {
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		return $this->getConfigValue('class_names/prefix', $dbName, $tableName)
		     . $this->getClassName($table)
		     . $this->getConfigValue('class_names/starter_object_suffix', $dbName, $tableName);
	}
	
	public function getStarterCollectionClassName(SchemaTable $table) {
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		return $this->getConfigValue('class_names/prefix', $dbName, $tableName)
		     . $this->getClassName($table)
		     . $this->getConfigValue('class_names/starter_collection_suffix', $dbName, $tableName);
	}
	
	public function getBaseObjectClassName(SchemaTable $table) {
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		return $this->getConfigValue('class_names/prefix', $dbName, $tableName)
		     . $this->getClassName($table)
		     . $this->getConfigValue('class_names/base_object_suffix', $dbName, $tableName);
	}
	
	public function getBaseCollectionClassName(SchemaTable $table) {
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		return $this->getConfigValue('class_names/prefix', $dbName, $tableName)
		     . $this->getClassName($table)
		     . $this->getConfigValue('class_names/base_collection_suffix', $dbName, $tableName);
	}
	
	public function getPhpdocAuthor(SchemaTable $table) {
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		return $this->getConfigValue('phpdoc/author', $dbName, $tableName);
	}
	
	public function getPhpdocPackage(SchemaTable $table) {
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		return $this->getConfigValue('phpdoc/package', $dbName, $tableName);
	}
	
	public function getPhpdocCopyright(SchemaTable $table) {
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		return $this->getConfigValue('phpdoc/copyright', $dbName, $tableName);
	}
	
	public function getObjectExtensionClassName(SchemaTable $table) {
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		return $this->getConfigValue('class_names/object_extension_class_name', $dbName, $tableName);
	}
	
	public function getCollectionExtensionClassName(SchemaTable $table) {
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		return $this->getConfigValue('class_names/collection_extension_class_name', $dbName, $tableName);
	}
	
	/**
	 * Converts an id field into an object name (in most cases this simply
	 * involves stripping off an "_id" suffix).
	 *
	 * @return mixed - string on successful conversion, false on failure
	 * @author Anthony Bush
	 **/
	public function convertIdToObjectName($table, $id) {
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		$idRegex = $this->getConfigValue('field_settings/id_regex', $dbName, $tableName);
		$matches = array();
		if (preg_match($idRegex, $id, $matches)) {
			return $matches[1];
		} else {
			return false;
		}
	}
	
	/**
	 * getTitleCase() takes the given string and returns it in TitleCase format
	 * (sometimes called UpperCamelCase), with underscores removed.
	 *
	 * Example input: db_column_name
	 * Example output: DbColumnName
	 * 
	 * @param string $value the string to convert to title case, usually containing underscores
	 * @return string the TitleCased version of the given string
	 * @author Anthony Bush
	 **/
	public function getTitleCase($value) {
		$value = str_replace('_', ' ', $value);
		$value = ucwords($value);
		$value = str_replace(' ', '', $value);
		return $value;
	}
	
	/**
	 * getCamelCase takes the given string and returns it in camelCase format,
	 * with underscores removed.
	 *
	 * Example input: db_column_name
	 * Example output: dbColumnName
	 *
	 * See: http://en.wikipedia.org/wiki/CamelCase
	 * 
	 * @param string $value the string to convert to camel case, usually containing underscores
	 * @return string the camelCased version of the given string
	 * @author Anthony Bush
	 **/
	public function getCamelCase($value) {
		$value = $this->getTitleCase($value);
		$value[0] = strtolower($value[0]);
		return $value;
	}
	
}

?>