<?php

/**
 * CoughGenerator takes config and schema and generates CoughClass objects.
 * 
 * @package CoughPHP
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
		if ($table->hasPrimaryKey()) {
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
		
		// Loop through all related one-to-one relationships and for any that require a
		// non-NULL foreign key (i.e. the relationship MUST exist) go ahead and change
		// the default SELECT SQL to INNER JOIN to that relationship.
		$selectSql = array('`' . $tableName . '`.*');
		$innerJoins = array();
		foreach ($table->getHasOneRelationships() as $hasOne) {
			if (!$hasOne->isKeyNullable($hasOne->getLocalKey()) && !in_array($hasOne->getRefTable()->getTableName(), $excludeTableJoins)) {
				$refDbName = $hasOne->getRefTable()->getDatabase()->getDatabaseName();
				$refTableName = $hasOne->getRefTable()->getTableName();
				$localKey = $hasOne->getLocalKey();

				$joinOnSql = array();
				foreach ($hasOne->getRefKey() as $index => $refColumn) {
					$joinOnSql[] = '`' . $tableName . '`.`' . $localKey[$index]->getColumnName() . '` = `' . $refTableName . '`.`' . $refColumn->getColumnName() . '`';
				}
				
				// Append to INNER JOIN SQL.
				$innerJoins['`' . $refDbName . '`.`' . $refTableName . '`'] = $joinOnSql;

				// Append to SELECT SQL.
				foreach ($hasOne->getRefTable()->getColumns() as $columnName => $refColumn) {
					$selectSql[] = '`' . $refTableName . '`.`' . $columnName . '` AS `' . $refTableName . '.' . $columnName . '`';
				}
			}
		}
		
		$sql = $indent . "SELECT\n"
		     . $indent . "\t" . implode("\n$indent\t, ", $selectSql) . "\n"
		     . $indent . "FROM\n"
		     . $indent . "\t`$dbName`.`$tableName`";
		
		foreach ($innerJoins as $joinTable => $joinCriteria) {
			$sql .= "\n" . $indent . "\tINNER JOIN $joinTable"
			      . "\n" . $indent . "\t\tON " . implode("\n$indent\t\tAND ", $joinCriteria);
		}
		
		return $sql;
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
		ob_start();
		foreach ($table->getHasOneRelationships() as $hasOne) {
			$objectTitleCase = $this->config->getTitleCase($hasOne->getRefObjectName());
			$objectClassName = $this->config->getStarterObjectClassName($hasOne->getRefTable());
			$localVarName = $this->config->getCamelCase($hasOne->getRefTableName()); //'object';
			if ($localVarName == 'this') {
				// avoid naming conflict
				$localVarName = 'object';
			}
			$localKey = $hasOne->getLocalKey();
?>
	public function load<?php echo $objectTitleCase ?>_Object() {
		$<?php echo $localVarName ?> = <?php echo $objectClassName ?>::constructByKey(array(
<?php foreach ($hasOne->getRefKey() as $key => $column): ?>
			'<?php echo $column->getColumnName() ?>' => $this->get<?php echo $this->config->getTitleCase($localKey[$key]->getColumnName()) ?>(),
<?php endforeach; ?>
		));
		$this->set<?php echo $objectTitleCase ?>_Object($<?php echo $localVarName ?>);
	}

	public function get<?php echo $objectTitleCase ?>_Object() {
		return $this->getObject('<?php echo $hasOne->getRefObjectName() ?>');
	}

	public function set<?php echo $objectTitleCase ?>_Object($<?php echo $localVarName ?>) {
		$this->setObject('<?php echo $hasOne->getRefObjectName() ?>', $<?php echo $localVarName ?>);
	}
	
<?php
		}
		$oneToOneMethods = ob_get_clean();
		
		
		// Generate one-to-many methods
		ob_start();
		$notifyCollections = array(); // store generated foreachs for the `notifyChildrenOfKeyChange()` method we will be generating.
		foreach ($table->getHasManyRelationships() as $hasMany) {
			$objectTitleCase = $this->config->getTitleCase($hasMany->getRefObjectName());
			$objectCamelCase = $this->config->getCamelCase($hasMany->getRefObjectName());
			$objectClassName = $this->config->getStarterObjectClassName($hasMany->getRefTable());
			$collectionClassName = $this->config->getStarterCollectionClassName($hasMany->getRefTable());
			$localKey = $hasMany->getLocalKey();
			$refKey = $hasMany->getRefKey();
			$refTableName = $hasMany->getRefTable()->getTableName();
			
			$notifySql = "\t\tforeach (\$this->get" . $objectTitleCase . "_Collection() as \$" . $objectCamelCase . ") {\n";
			foreach ($refKey as $key => $column) {
				$setter = 'set' . $this->config->getTitleCase($column->getColumnName());
				$notifySql .= "\t\t\t\$" . $objectCamelCase . "->" . $setter . "(\$key['" . $localKey[$key]->getColumnName() . "']);\n";
			}
			$notifySql .= "\t\t}";
			$notifyCollections[] = $notifySql;
			
			// Get the reference object name that will be used get*_Object,
			// set*_Object, load*_Object on the related objects.
			// e.g. if the current table we are generating for is order and has
			// a relation to order_line table via order_foo_id then
			// `$referralNameTitleCase` will hold OrderFoo and not Order.
			if (count($localKey) > 1) {
				// More than 1 key to make up the PK means we have to refer to
				// the object on the remote side using the local object name
				// instead of the remote_fk_id columns
				$referralName = $hasMany->getLocalObjectName();
			} else {
				// Only 1 key, use the "id" field to determine an appropriate
				// object name. If one can not be found, use the same one as in
				// the above case.
				$referralName = $this->config->convertIdToObjectName($hasMany->getRefTable(), $refKey[0]->getColumnName());
				if (!$referralName) {
					$referralName = $hasMany->getLocalObjectName();
				}
			}
			$referralNameTitleCase = $this->config->getTitleCase($referralName);

			// TODO: is `$localVarName` used?
			$localVarName = $this->config->getCamelCase($hasMany->getRefTableName());

			// Generate the criteria / WHERE clause information for loading the related object.
			$criteria = array();
			foreach ($refKey as $key => $column) {
				$criteria[] = '`' . $refTableName . '`.`' . $column->getColumnName() . '` = \' . $this->db->quote($this->get'
				            . $this->config->getTitleCase($localKey[$key]->getColumnName()) . '()) . \'';
			}

			// Build the loadSql for loading the related object.
			$loadSql = $this->generateLoadSql($hasMany->getRefTable(), array($table->getTableName()), "\t\t\t\t");
			if (count($criteria) > 0) {
				$loadSql .= "\n\t\t\t\tWHERE\n\t\t\t\t\t" . implode("\n\t\t\t\t\t", $criteria);
			}
?>
	public function load<?php echo $objectTitleCase ?>_Collection() {

		// Always create the collection
		$collection = new <?php echo $collectionClassName ?>();
		$this->set<?php echo $objectTitleCase ?>_Collection($collection);

		// But only populate it if we have key ID
		if ($this->hasKeyId()) {
			$sql = '<?php echo "\n" . $loadSql . "\n\t\t\t" ?>';

			// Construct and populate the collection
			$collection->loadBySql($sql);
			foreach ($collection as $element) {
				$element->set<?php echo $referralNameTitleCase ?>_Object($this);
			}
		}
	}

	public function get<?php echo $objectTitleCase ?>_Collection() {
		return $this->getCollection('<?php echo $hasMany->getRefObjectName() ?>');
	}

	public function set<?php echo $objectTitleCase ?>_Collection($<?php echo $localVarName ?>) {
		$this->setCollection('<?php echo $hasMany->getRefObjectName() ?>', $<?php echo $localVarName ?>);
	}

	public function add<?php echo $objectTitleCase ?>(<?php echo $objectClassName ?> $object) {
<?php
foreach ($hasMany->getRefKey() as $key => $column) {
	$setReferenceIdMethod = 'set' . $this->config->getTitleCase($column->getColumnName());
	$getLocalIdMethod = 'get' . $this->config->getTitleCase($localKey[$key]->getColumnName());
?>
		$object-><?php echo $setReferenceIdMethod ?>($this-><?php echo $getLocalIdMethod ?>());
<?php
}
?>
		$object->set<?php echo $referralNameTitleCase ?>_Object($this);
		$this->get<?php echo $objectTitleCase ?>_Collection()->add($object);
		return $object;
	}

	public function remove<?php echo $objectTitleCase ?>($objectOrId) {
		$removedObject = $this->get<?php echo $objectTitleCase ?>_Collection()->remove($objectOrId);
<?php foreach ($hasMany->getRefKey() as $key => $column): ?>
		$removedObject->set<?php echo $this->config->getTitleCase($column->getColumnName()) ?>(null);
<?php endforeach; ?>
		$removedObject->set<?php echo $referralNameTitleCase ?>_Object(null);
		return $removedObject;
	}

<?php
		}
		$oneToManyMethods = ob_get_clean();
		
		// Generate the `notifyChildrenOfKeyChange()` method if it will be non-empty.
		if (count($notifyCollections) > 0) {
			$notifyChildrenOfKeyChangeMethod = "\t" . 'public function notifyChildrenOfKeyChange(array $key) {'
			                                 . "\n" . implode("\n", $notifyCollections) . "\n\t}\n\t\n";
		} else {
			$notifyChildrenOfKeyChangeMethod = '';
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
	
	protected $dbAlias = '<?php echo $dbName ?>';
	
	protected $dbName = '<?php echo $dbName ?>';
	
	protected $tableName = '<?php echo $tableName ?>';
	
	protected $pkFieldNames = array('<?php echo implode("','", array_keys($table->getPrimaryKey())) ?>');
	
	protected $fields = array(
<?php foreach ($table->getColumns() as $columnName => $column): ?>
		'<?php echo $columnName ?>' => <?php echo $this->getStringFromPhpValue($column->getDefaultValue()) ?>,
<?php endforeach; ?>
	);
	
	protected $fieldDefinitions = array(
<?php foreach ($table->getColumns() as $columnName => $column): ?>
		'<?php echo $columnName ?>' => array(
			'db_column_name' => '<?php echo $columnName ?>',
			'is_null_allowed' => <?php echo $this->getStringFromPhpValue($column->isNullAllowed()) ?>,
			'default_value' => <?php echo $this->getStringFromPhpValue($column->getDefaultValue()) . "\n" ?>
		),
<?php endforeach; ?>
	);
	
	protected $objectDefinitions = array(
<?php foreach ($table->getHasOneRelationships() as $hasOne): ?>
		'<?php echo $hasOne->getRefObjectname() ?>' => array(
			'class_name' => '<?php echo $this->config->getStarterObjectClassName($hasOne->getRefTable()) ?>'
		),
<?php endforeach; ?>
	);
	
<?php
echo $notifyChildrenOfKeyChangeMethod;

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

if ($shouldOverrideLoadSqlWithoutWhere) {
?>
	public function getLoadSqlWithoutWhere() {
		return '<?php echo "\n" . $this->generateLoadSql($table, array(), "\t\t\t") . "\n\t\t" ?>';
	}
	
<?php
}
?>
	// Generated attribute accessors (getters and setters)

<?php echo $attributeMethods ?>
	// Generated one-to-one accessors (loaders, getters, and setters)

<?php echo $oneToOneMethods ?>
	// Generated one-to-many collection loaders, getters, setters, adders, and removers

<?php
echo $oneToManyMethods;

/* many-to-many methods disbaled for now...
?>

	// Generated many-to-many collection loaders, getters, setters, adders, and removers

<?php
foreach ($table->getHabtmRelationships() as $habtm) {
	$objectTitleCase = $this->config->getTitleCase($habtm->getRefObjectName());
	$objectClassName = $this->config->getStarterObjectClassName($habtm->getRefTable());
	$collectionClassName = $this->config->getStarterCollectionClassName($habtm->getRefTable());
	$localKey = $habtm->getLocalKey();
?>
	public function load<?php echo $objectTitleCase ?>_Collection() {
		
		// What are we collecting?
		$elementClassName = '<?php echo $objectClassName ?>';
		
		// Get the base SQL (so we can use the same SELECT and JOINs as the element class)
		$element = new $elementClassName();
		$sql = $element->getLoadSqlWithoutWhere();
		
		// What criteria are we using?
		$criteria = array(
<?php foreach ($habtm->getRefKey() as $key => $column): ?>
			'<?php echo $column->getColumnName() ?>' => $this->get<?php echo $this->config->getTitleCase($localKey[$key]->getColumnName()) ?>(),
<?php endforeach; ?>
		);
		$sql .= ' ' . $this->db->buildWhereSql($criteria);
		
		// Construct and populate the collection
		$collection = new <?php echo $collectionClassName ?>();
		$collection->loadBySql($sql);
		$this->set<?php echo $objectTitleCase ?>_Collection($collection);
	}

	public function get<?php echo $objectTitleCase ?>_Collection() {
		return $this->getCollection('<?php echo $habtm->getRefObjectName() ?>');
	}

	public function set<?php echo $objectTitleCase ?>_Collection($<?php echo $localVarName ?>) {
		$this->setCollection('<?php echo $habtm->getRefObjectName() ?>', $<?php echo $localVarName ?>);
	}

	public function add<?php echo $objectTitleCase ?>($objectOrId) {
		return $this->get<?php echo $objectTitleCase ?>_Collection()->add($objectOrId);
	}

	public function remove<?php echo $objectTitleCase ?>($objectOrId) {
		$removedObject = $this->get<?php echo $objectTitleCase ?>_Collection()->remove($objectOrId);
<?php foreach ($habtm->getRefKey() as $key => $columnName): ?>
		$removedObject->set<?php echo $this->config->getTitleCase($columnName) ?>(null);		
<?php endforeach; ?>
		return $removedObject;
	}

<?php
}

*/
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
class <?php echo $starterObjectClassName ?> extends <?php echo $baseObjectClassName ?> {
	
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
	public static function constructByKey($idOrHash) {
		$object = new <?php echo $starterObjectClassName ?>();
		if (is_array($idOrHash)) {
			$fields = $idOrHash;
		} else {
			$fields = array();
			foreach ($object->getPkFieldNames() as $fieldName) {
				$fields[$fieldName] = $idOrHash;
			}
		}
		$object->loadByCriteria($fields);
		if ($object->isInflated()) {
			return $object;
		} else {
			return null;
		}
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