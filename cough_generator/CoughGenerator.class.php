<?php

/**
 * CoughGenerator takes config and schema and generates CoughClass objects.
 * 
 * @package cough_generator
 * @author Anthony Bush
 **/
class CoughGenerator {
	
	/**
	 * Configuration object for this class
	 *
	 * @var DatabaseSchemaGeneratorConfig
	 **/
	protected $config = null;
	
	/**
	 * Storage for the generated CoughClass objects
	 *
	 * @var array
	 **/
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
		if ($this->config->shouldGenerateForTable($table)) {
			$this->generateBaseObject($table);
			$this->generateStarterObject($table);
			$this->generateBaseCollection($table);
			$this->generateStarterCollection($table);
		}
	}
	
	/**
	 * Get the generated classes
	 *
	 * @return array of CoughClass objects.
	 * @author Anthony Bush
	 **/
	public function getGeneratedClasses() {
		return $this->generatedClasses;
	}
	
	/**
	 * Add to the generated classes.
	 * 
	 * @param CoughClass $coughClass
	 * @return void
	 * @author Anthony Bush
	 **/
	public function addGeneratedClass($coughClass) {
		$this->generatedClasses[$coughClass->getClassName()] = $coughClass;
	}
	
	/**
	 * Generates an array of common phpDoc tags.
	 *
	 * @return array
	 * @author Anthony Bush
	 **/
	protected function generatePhpdocTags($table) {
		$phpdocTags = array();
		$phpdocAuthor = $this->config->getPhpdocAuthor($table);
		if (!empty($phpdocAuthor)) {
			$phpdocTags[] = '@author ' . $phpdocAuthor;
		}
		$phpdocPackage = $this->config->getPhpdocPackage($table);
		if (!empty($phpdocPackage)) {
			$phpdocTags[] = '@package ' . $phpdocPackage;
		}
		$phpdocPackage = $this->config->getPhpdocCopyright($table);
		if (!empty($phpdocPackage)) {
			$phpdocTags[] = '@copyright ' . $phpdocPackage;
		}
		return $phpdocTags;
	}
	
	/**
	 * Generate the loadSql for a given SchemaTable object (excluding WHERE clause).
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function generateLoadSql($table, $excludeTableJoins = array(), $indent = '') {
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		
		$escapedIndent = str_replace(array("\n", "\t"), array('\n', '\t'), $indent);
		
		$concreteClassName = $this->config->getStarterObjectClassName($table);
		
		// Loop through all related one-to-one relationships and for any that require a
		// non-NULL foreign key (i.e. the relationship MUST exist) go ahead and change
		// the default SELECT SQL to INNER JOIN to that relationship.
		
		$variables = array('$tableName = ' . $concreteClassName . '::getTableName();');
		$selectSql = array("`' . \$tableName . '`.*");
		$innerJoins = array();
		
		if ($this->config->shouldGenerateLoadSqlInnerJoins($table)) {
			foreach ($table->getHasOneRelationships() as $hasOne)
			{
				if (!$hasOne->isKeyNullable($hasOne->getLocalKey()) && !in_array($hasOne->getRefTable()->getTableName(), $excludeTableJoins))
				{
					$refDbName = $hasOne->getRefTable()->getDatabase()->getDatabaseName();
					$refTableName = $hasOne->getRefTable()->getTableName();
					$localKey = $hasOne->getLocalKey();

					$refTableAliasName = $this->config->getForeignTableAliasName($hasOne);
					$refObjectName = $this->config->getForeignObjectName($hasOne);
					$refConcreteClassName = $this->config->getStarterObjectClassName($hasOne->getRefTable());
					
					// We don't need to store these as we only use each reference table once (we alias them all)
					// $variables[] = '$tableName' . $refConcreteClassName . ' = ' . $refConcreteClassName . '::getTableName();';
					
					// Append to SELECT SQL.
					$selectSql[] = "' . implode(" . '"\n' . $escapedIndent . '\t, "' . ", CoughObject::getFieldAliases('$refConcreteClassName', '$refObjectName', '$refTableAliasName')) . '";
					
					// Rather than hard-code the columns, we'll use the newly added `getFieldAliases` static method (above line).
					// foreach ($hasOne->getRefTable()->getColumns() as $columnName => $refColumn) {
					// 	$selectSql[] = '`' . $refTableAliasName . '`.`' . $columnName . '` AS `' . $refObjectName . '.' . $columnName . '`';
					// }

					// Generate the INNER JOIN criteria
					$joinOnSql = array();
					foreach ($hasOne->getRefKey() as $index => $refColumn) {
						$joinOnSql[] = "`' . \$tableName . '`.`" . $localKey[$index]->getColumnName() . '` = `' . $refTableAliasName . '`.`' . $refColumn->getColumnName() . '`';
					}
					
					// Append to INNER JOIN SQL using the INNER JOIN criteria
					$joinTable = "`' . $refConcreteClassName::getDbName() . '`.`' . $refConcreteClassName::getTableName() . '` AS `" . $refTableAliasName . "`";
					$innerJoins[$joinTable] = $joinOnSql;
					
				}
			}
		}
		
		$sql = $indent . "SELECT\n"
		     . $indent . "\t" . implode("\n$indent\t, ", $selectSql) . "\n"
		     . $indent . "FROM\n"
		     . $indent . "\t`' . $concreteClassName::getDbName() . '`.`' . \$tableName . '`";
		
		foreach ($innerJoins as $joinTable => $joinCriteria) {
			$sql .= "\n$indent\tINNER JOIN $joinTable"
			      . "\n$indent\t\tON " . implode("\n$indent\t\tAND ", $joinCriteria);
		}
		
		return array($variables, $sql);
	}

	/**
	 * Generates the base object class
	 *
	 * @return string the generated PHP code
	 * @author Anthony Bush
	 **/
	protected function generateBaseObject($table) {
		
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		
		$starterObjectClassName     = $this->config->getStarterObjectClassName($table);
		$starterCollectionClassName = $this->config->getStarterCollectionClassName($table);
		$baseObjectClassName        = $this->config->getBaseObjectClassName($table);
		$baseCollectionClassName    = $this->config->getBaseCollectionClassName($table);
		
		$phpdocTags = $this->generatePhpdocTags($table);
		$phpdocTags[] = '@see ' . $starterObjectClassName . ', CoughObject';
		
		$extensionClassName = $this->config->getObjectExtensionClassName($table);
		
		
		// Generate attribute methods
		ob_start();
		foreach ($table->getColumns() as $columnName => $column) {
			$titleCase = $this->config->getTitleCase($columnName);
?>
	public function get<?php echo $titleCase ?>() {
		return $this->getField('<?php echo $columnName ?>');
	}
	
	public function set<?php echo $titleCase ?>($value) {
		$this->setField('<?php echo $columnName ?>', $value);
	}
	
<?php
		}
		$attributeMethods = ob_get_clean();
		
		
		// Generate one-to-one methods
		$oneToOneMethods = '';
		$objectDefinitions = array();
		foreach ($table->getHasOneRelationships() as $hasOne) {
			
			if (!$this->config->shouldGenerateHasOneMethods($hasOne)) {
				continue;
			}
			
			$objectClassName = $this->config->getStarterObjectClassName($hasOne->getRefTable());
			$entityName = $this->config->getForeignTableAliasName($hasOne); // e.g. 'billing_address'
			$objectName = $this->config->getForeignObjectName($hasOne); // e.g. 'BillingAddress_Object'
			
			// Get the local variable name
			$localVarName = $this->config->getCamelCase($entityName); // e.g. 'billingAddress';
			if ($localVarName == 'this') {
				// avoid naming conflict by renaming a "this" object name to "object"
				$localVarName = 'object';
			}
			
			$localKey = $hasOne->getLocalKey();
			
			$objectDefinitions[] = "\n\t\t'" . $objectName . "' => array("
			                     . "\n\t\t\t'class_name' => '" . $objectClassName . "'\n\t\t),";
			
			// generate load*_Object()
			$oneToOneMethods .= "\tpublic function load" . $objectName . "() {\n";
			$refKey = $hasOne->getRefKey();
			if (count($refKey) == 1)
			{
				// Only one column key -- generate simplified code (one line)
				reset($refKey);
				list($key) = each($refKey);
				$titleCase = $this->config->getTitleCase($localKey[$key]->getColumnName());
				$oneToOneMethods .= "\t\t\$this->set" . $objectName . "(" . $objectClassName . "::constructByKey(\$this->get" . $titleCase . "()));\n";
			}
			else
			{
				// Multi-column key
				$oneToOneMethods .= "\t\t\$tableName = " . $objectClassName . "::getTableName();\n"
				                  . "\t\t\$" . $localVarName . " = " . $objectClassName . "::constructByKey(array(\n";
				foreach ($refKey as $key => $column)
				{
					$titleCase = $this->config->getTitleCase($localKey[$key]->getColumnName());
					$oneToOneMethods .= "\t\t\t'`' . \$tableName . '`.`" . $column->getColumnName() . "`' => \$this->get" . $titleCase . "(),\n";
				}
				$oneToOneMethods .= "\t\t));\n"
				                  . "\t\t\$this->set" . $objectName . "(\$" . $localVarName . ");\n";
			}
			$oneToOneMethods .= "\t}\n\t\n";
			
			// generate get*_Object()
			$oneToOneMethods .= "\tpublic function get" . $objectName . "() {\n"
			                  . "\t\tif (!isset(\$this->objects['" . $objectName . "'])) {\n"
			                  . "\t\t\t\$this->load" . $objectName . "();\n"
			                  . "\t\t}\n"
			                  . "\t\treturn \$this->objects['" . $objectName . "'];\n"
			                  . "\t}\n\t\n";
			
			// generate set*_Object()
			$oneToOneMethods .= "\tpublic function set" . $objectName . "(\$" . $localVarName . ") {\n"
			                  . "\t\t\$this->objects['" . $objectName . "'] = \$" . $localVarName . ";\n"
			                  . "\t}\n\t\n";
		}
		
		// Generate the objectDefinitions parameter
		if (empty($objectDefinitions)) {
			$objectDefinitionsPhp = "\t" . 'protected $objectDefinitions = array();' . "\n\t\n";
		} else {
			$objectDefinitionsPhp = "\t" . 'protected $objectDefinitions = array(' . implode('', $objectDefinitions) . "\n\t" . ');' . "\n\t\n";
		}
		
		// Generate one-to-many methods
		ob_start();
		$notifyCollections = array(); // store generated foreachs for the `notifyChildrenOfKeyChange()` method we will be generating.
		foreach ($table->getHasManyRelationships() as $hasMany) {
			
			if (!$this->config->shouldGenerateHasManyMethods($hasMany)) {
				continue;
			}
			
			$localCollectionName = $this->config->getForeignCollectionName($hasMany, $table->getHasManyRelationships()); // e.g. WflTicket_Collection_ByBillingAddressId
			$localCollectionClassName = $this->config->getStarterCollectionClassName($hasMany->getRefTable()); // e.g. usr_WflTicket_Collection
			
			// Some debug....
			// echo 'Generating ' . $baseObjectClassName . "<br />\n";
			// echo 'Ref Table Name: ' . $hasMany->getRefTable()->getTableName() . "<br />\n";
			// echo 'Local Table Name: ' . $hasMany->getLocalTable()->getTableName() . "<br />\n";
			// echo 'Ref Keys: ';
			// foreach ($hasMany->getRefKey() as $column) {
			// 	echo $column->getColumnName() . ',';
			// }
			// echo "<br />\n";
			// echo 'Local Keys: ';
			// foreach ($hasMany->getLocalKey() as $column) {
			// 	echo $column->getColumnName() . ',';
			// }
			// echo "<br />\n";
			// echo 'Local collection name: ' . $localCollectionName . "<br />\n";
			// echo 'Local collection class name: ' . $localCollectionClassName . "<br />\n";
			// die();
			
			// 2007-10-24/AWB: TODO: make this better
			$shouldGenerateAddersAndRemovers = true;
			if (strpos($localCollectionName, '_By') !== false) {
				$shouldGenerateAddersAndRemovers = false;
			}
			
			$refEntityName = $this->config->getEntityName($hasMany->getRefTable()); // e.g. wfl_ticket
			$refObjectTitleCase = $this->config->getTitleCase($refEntityName); // e.g. WflTicket
			$refObjectCamelCase = $this->config->getCamelCase($refEntityName); // e.g. wflTicket
			$refObjectClassName = $this->config->getStarterObjectClassName($hasMany->getRefTable()); // e.g. usr_WflTicket
			
			$localKey = $hasMany->getLocalKey();
			$refKey = $hasMany->getRefKey();
			
			$refObjectName = $this->config->getForeignObjectName($hasMany->getHasOneRelationship()); // e.g. BillingAddress_Object
			$refTableAliasName = $hasMany->getRefTable()->getTableName(); // $this->config->getForeignTableAliasName($hasMany->getHasOneRelationship()); // e.g. billing_address
			$localVarName = $refObjectCamelCase . 'Collection';
			
			$notifySql = "\t\tforeach (\$this->get" . $localCollectionName . "() as \$" . $refObjectCamelCase . ") {\n";
			foreach ($refKey as $key => $column) {
				$setter = 'set' . $this->config->getTitleCase($column->getColumnName());
				$notifySql .= "\t\t\t\$" . $refObjectCamelCase . "->" . $setter . "(\$key['" . $localKey[$key]->getColumnName() . "']);\n";
			}
			$notifySql .= "\t\t}";
			$notifyCollections[] = $notifySql;
			
			// Generate the criteria / WHERE clause information for loading the related object.
			$criteria = array();
			foreach ($refKey as $key => $column) {
				$criteria[] = "`' . \$tableName . '`.`" . $column->getColumnName() . '` = \' . $db->quote($this->get'
				            . $this->config->getTitleCase($localKey[$key]->getColumnName()) . '()) . \'';
			}

			// Build the loadSql for loading the related object.
			list($variables, $loadSql) = $this->generateLoadSql($hasMany->getRefTable(), array($table->getTableName()), "\t\t\t\t");
			if (count($criteria) > 0) {
				$loadSql .= "\n\t\t\t\tWHERE\n\t\t\t\t\t" . implode("\n\t\t\t\t\t", $criteria);
			}
?>
	public function load<?php echo $localCollectionName ?>() {
		
		// Always create the collection
		$collection = new <?php echo $localCollectionClassName ?>();
		$this->set<?php echo $localCollectionName ?>($collection);
		
		// But only populate it if we have key ID
		if ($this->hasKeyId()) {
			$db = <?php echo $refObjectClassName; ?>::getDb();
			<?php echo implode("\n\t\t\t", $variables) . "\n" ?>
			$sql = '<?php echo "\n" . $loadSql . "\n\t\t\t" ?>';

			// Construct and populate the collection
			$collection->loadBySql($sql);
			foreach ($collection as $element) {
				$element->set<?php echo $refObjectName ?>($this);
			}
		}
	}
	
	public function get<?php echo $localCollectionName ?>() {
		if (!isset($this->collections['<?php echo $localCollectionName ?>'])) {
			$this->load<?php echo $localCollectionName ?>();
		}
		return $this->collections['<?php echo $localCollectionName ?>'];
	}
	
	public function set<?php echo $localCollectionName ?>($<?php echo $localVarName ?>) {
		$this->collections['<?php echo $localCollectionName ?>'] = $<?php echo $localVarName ?>;
	}
	
<?php if ($shouldGenerateAddersAndRemovers): ?>
	public function add<?php echo $refObjectTitleCase ?>(<?php echo $refObjectClassName ?> $object) {
<?php
foreach ($hasMany->getRefKey() as $key => $column) {
	$setReferenceIdMethod = 'set' . $this->config->getTitleCase($column->getColumnName());
	$getLocalIdMethod = 'get' . $this->config->getTitleCase($localKey[$key]->getColumnName());
?>
		$object-><?php echo $setReferenceIdMethod ?>($this-><?php echo $getLocalIdMethod ?>());
<?php
}
?>
		$object->set<?php echo $refObjectName ?>($this);
		$this->get<?php echo $localCollectionName ?>()->add($object);
		return $object;
	}
	
	public function remove<?php echo $refObjectTitleCase ?>($objectOrId) {
		$removedObject = $this->get<?php echo $localCollectionName ?>()->remove($objectOrId);
		if (is_object($removedObject)) {
<?php foreach ($hasMany->getRefKey() as $key => $column): ?>
			$removedObject->set<?php echo $this->config->getTitleCase($column->getColumnName()) ?>(<?php echo $this->getDefaultValueStringForColumn($column) ?>);
<?php endforeach; ?>
			$removedObject->set<?php echo $refObjectName ?>(null);
		}
		return $removedObject;
	}
	
<?php endif; ?>
<?php
		}
		$oneToManyMethods = ob_get_clean();
		
		// Generate the `notifyChildrenOfKeyChange()` method if it will be non-empty.
		if (count($notifyCollections) > 0) {
			$notifyChildrenOfKeyChangePhp = "\t" . 'public function notifyChildrenOfKeyChange(array $key) {'
			                              . "\n" . implode("\n", $notifyCollections) . "\n\t}\n\t\n";
		} else {
			$notifyChildrenOfKeyChangePhp = '';
		}
		
		// Loop through all related one-to-one relationships and if any require a
		// non-NULL foreign key (i.e. the relationship MUST exist) go ahead and
		// override the default getLoadSqlWithoutWhere method.
		$shouldOverrideLoadSqlWithoutWhere = false;
		foreach ($table->getHasOneRelationships() as $hasOne) {
			if (!$hasOne->isKeyNullable($hasOne->getLocalKey())) {
				$shouldOverrideLoadSqlWithoutWhere = true;
				break;
			}
		}

		// Generate the `getLoadSqlWithoutWhere()` method if needed
		// NOTE: we always generate this in PHP < 5.3 (no "static" keyword => have to generate all static methods)
		if (true || $shouldOverrideLoadSqlWithoutWhere) {
			list($variables, $sql) = $this->generateLoadSql($table, array(), "\t\t\t");
			
			$getLoadSqlWithoutWherePhp
			    = "\tpublic static function getLoadSql() {"
			    . "\n\t\t" . implode("\n\t\t", $variables)
			    . "\n\t\treturn '\n" . $sql . "\n\t\t';"
			    . "\n\t}\n\t\n";
		} else {
			$getLoadSqlWithoutWherePhp = '';
		}
		
		
		// Generate class
		ob_start();
		echo("<?php\n\n");
		?>
/**
 * This is the base class for <?php echo $starterObjectClassName ?>.
 * 
 * <?php echo implode("\n * ", $phpdocTags) . "\n"; ?>
 **/
abstract class <?php echo $baseObjectClassName ?> extends <?php echo $extensionClassName ?> {
	
	protected static $db = null;
	protected static $dbName = '<?php echo $dbName; ?>';
	protected static $tableName = '<?php echo $tableName; ?>';
	protected static $pkFieldNames = array('<?php echo implode("','", array_keys($table->getPrimaryKey())) ?>');
	
	protected $fields = array(
<?php foreach ($table->getColumns() as $columnName => $column): ?>
		'<?php echo $columnName ?>' => <?php echo $this->getDefaultValueStringForColumn($column) ?>,
<?php endforeach; ?>
	);
	
	protected $fieldDefinitions = array(
<?php foreach ($table->getColumns() as $columnName => $column): ?>
		'<?php echo $columnName ?>' => array(
			'db_column_name' => '<?php echo $columnName ?>',
			'is_null_allowed' => <?php echo $this->getStringFromPhpValue($column->isNullAllowed()) ?>,
			'default_value' => <?php echo $this->getDefaultValueStringForColumn($column) . "\n" ?>
		),
<?php endforeach; ?>
	);
	
<?php
echo $objectDefinitionsPhp;
?>
	// Static Definition Methods
	
	public static function getDb() {
		if (is_null(<?php echo $starterObjectClassName ?>::$db)) {
			<?php echo $starterObjectClassName ?>::$db = CoughDatabaseFactory::getDatabase(<?php echo $starterObjectClassName ?>::$dbName);
		}
		return <?php echo $starterObjectClassName ?>::$db;
	}
	
	public static function getDbName() {
		return CoughDatabaseFactory::getDatabaseName(<?php echo $starterObjectClassName ?>::$dbName);
	}
	
	public static function getTableName() {
		return <?php echo $starterObjectClassName ?>::$tableName;
	}
	
	public static function getPkFieldNames() {
		return <?php echo $starterObjectClassName ?>::$pkFieldNames;
	}
	
	// Static Construction (factory) Methods
	
	/**
	 * Constructs a new <?php echo $starterObjectClassName ?> object from
	 * a single id (for single key PKs) or a hash of [field_name] => [field_value].
	 * 
	 * The key is used to pull data from the database, and, if no data is found,
	 * null is returned. You can use this function with any unique keys or the
	 * primary key as long as a hash is used. If the primary key is a single
	 * field, you may pass its value in directly without using a hash.
	 * 
	 * @param mixed $idOrHash - id or hash of [field_name] => [field_value]
	 * @return mixed - <?php echo $starterObjectClassName ?> or null if no record found.
	 **/
	public static function constructByKey($idOrHash, $forPhp5Strict = '') {
		return CoughObject::constructByKey($idOrHash, '<?php echo $starterObjectClassName ?>');
	}
	
	/**
	 * Constructs a new <?php echo $starterObjectClassName ?> object from custom SQL.
	 * 
	 * @param string $sql
	 * @return mixed - <?php echo $starterObjectClassName ?> or null if exactly one record could not be found.
	 **/
	public static function constructBySql($sql, $forPhp5Strict = '') {
		return CoughObject::constructBySql($sql, '<?php echo $starterObjectClassName ?>');
	}
	
	/**
	 * Constructs a new <?php echo $starterObjectClassName ?> object after
	 * checking the fields array to make sure the appropriate subclass is
	 * used.
	 * 
	 * No queries are run against the database.
	 * 
	 * @param array $hash - hash of [field_name] => [field_value] pairs
	 * @return <?php echo $starterObjectClassName . "\n" ?>
	 **/
	public static function constructByFields($hash) {
		return new <?php echo $starterObjectClassName ?>($hash);
	}
	
<?php
echo $notifyChildrenOfKeyChangePhp;
echo $getLoadSqlWithoutWherePhp;
echo "\t" . '// Generated attribute accessors (getters and setters)' . "\n\t\n";
echo $attributeMethods;
echo "\t" . '// Generated one-to-one accessors (loaders, getters, and setters)' . "\n\t\n";
echo $oneToOneMethods;
echo "\t" . '// Generated one-to-many collection loaders, getters, setters, adders, and removers' . "\n\t\n";
echo $oneToManyMethods;
?>
}
<?php
		echo("\n?>");
		
		// Add the class
		$class = new CoughClass();
		$class->setContents(ob_get_clean());
		$class->setIsStarterClass(false);
		$class->setIsCollectionClass(false);
		$class->setClassName($baseObjectClassName);
		$class->setDatabaseName($dbName);
		$class->setTableName($tableName);
		$this->addGeneratedClass($class);
	}

	/**
	 * Generates the base collection class
	 *
	 * @return string the generated PHP code
	 * @author Anthony Bush
	 **/
	protected function generateBaseCollection($table) {
		
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		
		$starterObjectClassName     = $this->config->getStarterObjectClassName($table);
		$starterCollectionClassName = $this->config->getStarterCollectionClassName($table);
		$baseObjectClassName        = $this->config->getBaseObjectClassName($table);
		$baseCollectionClassName    = $this->config->getBaseCollectionClassName($table);
		
		$phpdocTags = $this->generatePhpdocTags($table);
		$phpdocTags[] = '@see ' . $starterCollectionClassName . ', CoughCollection';
		
		$extensionClassName = $this->config->getCollectionExtensionClassName($table);
		
		ob_start();
		echo("<?php\n\n");
		?>
/**
 * This is the base class for <?php echo $starterCollectionClassName ?>.
 *
 * <?php echo implode("\n * ", $phpdocTags) . "\n"; ?>
 **/
abstract class <?php echo $baseCollectionClassName ?> extends <?php echo $extensionClassName ?> {
	protected $dbAlias = '<?php echo $dbName ?>';
	protected $dbName = '<?php echo $dbName ?>';
	protected $elementClassName = '<?php echo $starterObjectClassName ?>';
}
<?php
		echo("\n?>");
		
		// Add the class
		$class = new CoughClass();
		$class->setContents(ob_get_clean());
		$class->setIsStarterClass(false);
		$class->setIsCollectionClass(true);
		$class->setClassName($baseCollectionClassName);
		$class->setDatabaseName($dbName);
		$class->setTableName($tableName);
		$this->addGeneratedClass($class);
	}

	/**
	 * Generates the starter object class
	 *
	 * @return string the generated PHP code
	 * @author Anthony Bush
	 **/
	protected function generateStarterObject($table) {
		
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		
		$starterObjectClassName     = $this->config->getStarterObjectClassName($table);
		$starterCollectionClassName = $this->config->getStarterCollectionClassName($table);
		$baseObjectClassName        = $this->config->getBaseObjectClassName($table);
		$baseCollectionClassName    = $this->config->getBaseCollectionClassName($table);
		
		$phpdocTags = $this->generatePhpdocTags($table);
		$phpdocTags[] = '@see ' . $baseObjectClassName . ', CoughObject';
		
		ob_start();
		echo("<?php\n\n");
		?>
/**
 * This is the starter class for <?php echo $baseObjectClassName ?>.
 *
 * <?php echo implode("\n * ", $phpdocTags) . "\n"; ?>
 **/
class <?php echo $starterObjectClassName ?> extends <?php echo $baseObjectClassName ?> implements CoughObjectStaticInterface {
}
<?php
		echo("\n?>");
		
		// Add the class
		$class = new CoughClass();
		$class->setContents(ob_get_clean());
		$class->setIsStarterClass(true);
		$class->setIsCollectionClass(false);
		$class->setClassName($starterObjectClassName);
		$class->setDatabaseName($dbName);
		$class->setTableName($tableName);
		$this->addGeneratedClass($class);
	}

	/**
	 * Generates the starter collection class
	 *
	 * @return string the generated PHP code
	 * @author Anthony Bush
	 **/
	protected function generateStarterCollection($table) {
		
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		
		$starterObjectClassName     = $this->config->getStarterObjectClassName($table);
		$starterCollectionClassName = $this->config->getStarterCollectionClassName($table);
		$baseObjectClassName        = $this->config->getBaseObjectClassName($table);
		$baseCollectionClassName    = $this->config->getBaseCollectionClassName($table);
		
		$phpdocTags = $this->generatePhpdocTags($table);
		$phpdocTags[] = '@see ' . $baseCollectionClassName . ', CoughCollection';
		
		ob_start();
		echo("<?php\n\n");
		?>
/**
* This is the starter class for <?php echo $baseCollectionClassName ?>.
 *
 * <?php echo implode("\n * ", $phpdocTags) . "\n"; ?>
 **/
class <?php echo $starterCollectionClassName ?> extends <?php echo $baseCollectionClassName ?> {
}
<?php
		echo("\n?>");
		
		// Add the class
		$class = new CoughClass();
		$class->setContents(ob_get_clean());
		$class->setIsStarterClass(true);
		$class->setIsCollectionClass(true);
		$class->setClassName($starterCollectionClassName);
		$class->setDatabaseName($dbName);
		$class->setTableName($tableName);
		$this->addGeneratedClass($class);
	}

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
	 * Converts a PHP primitive into a value safe for outputting into PHP code.
	 * 
	 * @param mixed $phpValue - null, boolean, string (not array or objects)
	 * @return string
	 * @author Anthony Bush
	 **/
	private function getStringFromPhpValue($phpValue) {
		if ($phpValue === null) {
			return 'null';
		} else if ($phpValue === false) {
			return 'false';
		} else if ($phpValue === true) {
			return 'true';
		} else {
			return '"' . addslashes($phpValue) . '"';
		}
	}
	
	private function getDefaultValueStringForColumn(SchemaColumn $column) {
		$defaultValue = $column->getDefaultValue();
		if (in_array(strtolower($column->getType()), array('int', 'tinyint'))) {
			if ($defaultValue === null) {
				return 'null';
			} else if (strlen($defaultValue) == 0) {
				return '""';
			} else {
				return $defaultValue;
			}
		} else {
			return $this->getStringFromPhpValue($defaultValue);
		}
	}
	
	
	
	
	##################
	# ERROR HANDLING #
	##################
	
	
	
	/**
	 * getErrorMesages() returns an array of error messages, if any.
	 *
	 * @return array of strings, each an error message (empty array if none).
	 * @author Anthony Bush
	 **/
	public function getErrorMessages() {
		return $this->errorMessages;
	}
	
	/**
	 * Logs the given exception (currently just saves the message to the error array)
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function logException(&$e) {
		$this->errorMessages[] = $e->getMessage();
	}
	
	public function getWarnings() {
		return $this->warnings;
	}
	
}

?>
