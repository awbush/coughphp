<?php

/**
 * Config class for CoughGenerator.
 * 
 * It abstracts the implementation of configuration specification, as well
 * as makes it easy for the CoughGenerator to perform different actions
 * based on the user's configuration.
 *
 * @package cough_generator
 * @author Anthony Bush
 **/
class CoughGeneratorConfig extends CoughConfig {
	
	public static function constructFromFile($filePath) {
		include($filePath);
		return new CoughGeneratorConfig($config);
	}
	
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
			'load_sql_inner_joins' => 'disabled', // valid options: enabled, disabled
			'generate_has_one_methods' => 'all', // valid options: all, none, or array of databases to generate join methods for.
			'generate_has_many_methods' => 'all', // valid options: all, none, or array of databases to generate join methods for.
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
			$className = $this->getTitleCase($this->getEntityName($table));
		}
		
		return $className;
	}
	
	public function getTableNameWithoutPrefix($tableName, $prefixes) {
		$tableNameWithoutPrefix = $tableName;
		foreach ($prefixes as $prefix) {
			if (substr($tableName, 0, strlen($prefix)) == $prefix) {
				$tableNameWithoutPrefix = substr($tableName, strlen($prefix));
				break;
			}
		}
		return $tableNameWithoutPrefix;
	}
	
	public function getClassFilePath(CoughClass $class) {
		if ($class->isStarterClass()) {
			return $this->getConfigValue('paths/starter_classes', $class->getDatabaseName(), $class->getTableName());
		} else {
			return $this->getConfigValue('paths/generated_classes', $class->getDatabaseName(), $class->getTableName());
		}
	}
	
	public function getClassFileName(CoughClass $class) {
		$filePath = $this->getClassFilePath($class);
		$fileSuffix = $this->getConfigValue('paths/file_suffix', $class->getDatabaseName(), $class->getTableName());
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
	 * Example input -> output might be "billing_addres_id" -> "billing_address"
	 *
	 * @return mixed - string on successful conversion, false on failure
	 * @author Anthony Bush
	 **/
	public function convertIdToEntityName($table, $id) {
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
	 * Given a table object, it gets the "entity name", currently just strips
	 * any prefixes specified in the config from the table name, otherwise just
	 * returns the table name.
	 * 
	 * e.g. "cust_address" will get entity name "address" if
	 * strip_table_name_prefixes has 'cust_' in it.
	 * 
	 *
	 * @return string
	 * @author Anthony Bush
	 **/
	public function getEntityName(SchemaTable $table) {
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		
		// Check if a custom entity name is set via the configuration, if not, then use strip_table_name_prefixes option.
		$entityName = $this->getConfigValue('entity_name', $dbName, $tableName, CoughConfig::SCOPE_TABLE);
		if (is_null($entityName)) {
			// If a prefix exists in the table name, remove it
			$prefixes = $this->getConfigValue('class_names/strip_table_name_prefixes', $dbName, $tableName);
			$entityName = $this->getTableNameWithoutPrefix($tableName, $prefixes);
		}
		
		return $entityName;
	}
	
	public function getForeignTableAliasName(SchemaRelationship $relationship) {
		$localKey = $relationship->getLocalKey();
		
		// If there is only one local key and we can parse out a value from id_regex, then use that.
		if (count($localKey) == 1) {
			// Use field name.
			$entityName = $this->convertIdToEntityName($relationship->getLocalTable(), $localKey[0]->getColumnName());
			if ($entityName !== false) {
				// We got a valid entity name
				return $entityName;
			}
		}
		
		// First check if there is more than one link to the reference table. If so, then
		// we will hit collision of names so we should instead just use the column name
		// as the entity name.  Otherwise, we can use the table name as the entity name.
		// This resolves bug: https://bugs.launchpad.net/coughphp/+bug/284702
		
		$refTable = $relationship->getRefTable();
		$numRefTableLinks = 0;
		foreach ($relationship->getLocalTable()->getHasOneRelationships() as $hasOneRelationship) {
			if ($hasOneRelationship->getRefTable() === $refTable) {
				++$numRefTableLinks;
			}
		}
		
		if ($numRefTableLinks > 1)
		{
			// Use column names that make up the local key as the entity name (hopefully only
			// one column in this case, otherwise the name won't be very meaningful).
			$entityName = '';
			foreach ($localKey as $column)
			{
				$entityName .= '_' . $column->getColumnName();
			}
			return $entityName;
		}
		else
		{
			// Use reference table's "entity name."
			return $this->getEntityName($relationship->getRefTable());
		}
	}
	
	public function getForeignObjectName(SchemaRelationship $relationship) {
		$entityName = $this->getForeignTableAliasName($relationship);
		return $this->getTitleCase($entityName) . '_Object';
	}
	
	public function getForeignCollectionName($relationship, $relationships) {
		// Step 1: loop through all relationships and count the number of
		// collections to the table in question. If we have more than one, then
		// we need to generate more unique name, e.g.
		// Addresss::getOrder_Collection_ByBillingAddressId.
		// If we don't have more than one, then use the naming without the "_By"
		// section, e.g. Address::getOrder_Collection
		
		$tableName = $relationship->getRefTable()->getTableName();
		
		$numPotentialCollections = 0;
		$numResolveableCollections = 0;
		foreach ($relationships as $rel) {
			if ($rel->getRefTable()->getTableName() == $tableName) {
				$numPotentialCollections++;
				$refKey = $rel->getRefKey();
				if (count($refKey) == 1) {
					$numResolveableCollections++;
				}
			}
		}
		
		$entityName = $this->getEntityName($relationship->getRefTable());
		$refKey = $relationship->getRefKey();
		$baseName = $this->getTitleCase($entityName) . '_Collection';
		
		// If there is only one relationship to the other table OR we don't
		// have a single key relationship, then use the base name.
		if ($numPotentialCollections <= 1 || count($refKey) !== 1) {
			return $baseName;
		}
		
		// Add the remote field name to the base name
		$value = $baseName . '_By' . $this->getTitleCase($refKey[0]->getColumnName());
		return $value;
	}
	
	/**
	 * Returns an array of regexes to match against in order to determine if a
	 * field should be considered an ID field, in order of precedence.
	 *
	 * @return array
	 * @author Anthony Bush
	 **/
	public function getIdRegex(SchemaTable $table) {
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		$idRegex = $this->getConfigValue('field_settings/id_regex', $dbName, $tableName);
		if (!is_array($idRegex)) {
			$idRegex = array($idRegex);
		}
		return $idRegex;
	}
	
	public function shouldGenerateLoadSqlInnerJoins(SchemaTable $table) {
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		$option = $this->getConfigValue('load_sql_inner_joins', $dbName, $tableName);
		if ($option == 'enabled') {
			return true;
		} else {
			return false;
		}
	}
	
	public function shouldGenerateForTable(SchemaTable $table) {
		if (!$table->hasPrimaryKey()) {
			return false;
		}
		return true;
	}
	
	public function shouldGenerateHasOneMethods(SchemaRelationship $hasOne) {
		// Don't generate links to tables we won't generate classes for.
		if (!$this->shouldGenerateForTable($hasOne->getRefTable())) {
			return false;
		}
		$table = $hasOne->getLocalTable();
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		$option = $this->getConfigValue('generate_has_one_methods', $dbName, $tableName);
		if (is_array($option)) {
			// yes, but only for databases in the array.
			$refDbName = $hasOne->getRefTable()->getDatabase()->getDatabaseName();
			if (in_array($refDbName, $option)) {
				return true;
			}
		} else if ($option == 'all') {
			return true;
		}
		return false;
	}
	
	public function shouldGenerateHasManyMethods(SchemaRelationship $hasMany) {
		// Don't generate links to tables we won't generate classes for.
		if (!$this->shouldGenerateForTable($hasMany->getRefTable())) {
			return false;
		}
		$table = $hasMany->getLocalTable();
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		$option = $this->getConfigValue('generate_has_many_methods', $dbName, $tableName);
		if (is_array($option)) {
			// yes, but only for databases in the array.
			$refDbName = $hasMany->getRefTable()->getDatabase()->getDatabaseName();
			if (in_array($refDbName, $option)) {
				return true;
			}
		} else if ($option == 'all') {
			return true;
		}
		return false;
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
