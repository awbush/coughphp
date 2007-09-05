<?php

class CoughGenerator {
	
	/**
	 * Configuration object for this class
	 *
	 * @var DatabaseSchemaGeneratorConfig
	 **/
	protected $config = null;
	
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
		
		// Setup some shortcuts
		// $table      =& $this->table;
		// $tableLinks =& $this->table['table_links'];
		// $oneToOneLinks   =& $this->table['table_links']['oto'];
		// $oneToManyLinks  =& $this->table['table_links']['otm'];
		// $manyToManyLinks =& $this->table['table_links']['mtm'];
		// $primaryKeyName = $this->getPrimaryKeyName();

		ob_start();
		echo("<?php\n\n");
		?>
/**
 * This is the base class for <?php echo $starterObjectClassName ?>.
 * 
 * <?php echo implode("\n * ", $phpdocTags) . "\n"; ?>
 **/
abstract class <?php echo $baseObjectClassName ?> extends <?php echo $extensionClassName ?> {
	
	protected $dbName = '<?php echo $dbName ?>';
	
	protected $tableName = '<?php echo $tableName ?>';
	
	protected $pkFieldNames = array('<?php echo implode("','", array_keys($table->getPrimaryKey())) ?>');
	
	protected $fields = array(
<?php foreach ($table->getColumns() as $columnName => $column): ?>
		'<?php echo $columnName ?>' => <?php $this->getStringFromPhpValue($column->getDefaultValue()) ?>,
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

<?php /*
	protected $collectionDefinitions = array(

		// One-to-many collections
<?php
	foreach ($table->getHasManyRelationships() as $hasMany):
		// TODO: Do we need this check?
		// many-to-many collection takes precedence.
		// if (isset($manyToManyLinks[$linkTableName])) {
		// 	continue;
		// }
		
		// Set the values of retired column and set/not set values, but only if the linked table has the retired column.
		if (isset($this->tables[$linkTableName]['variables'][$this->retiredColumn])) {
			$retiredColumn = "'" . $this->retiredColumn . "'";
			$isRetiredValue = "'" . $this->isRetiredValue . "'";
			$isNotRetiredValue = "'" . $this->isNotRetiredValue . "'";
		} else {
			$retiredColumn = 'null';
			$isRetiredValue = 'null';
			$isNotRetiredValue = 'null';
		}
?>
		'<?php echo $link['object_camel_name'] ?>' => array(
			'element_class' => '<?php echo $link['element_class_name'] ?>',
			'collection_class' => '<?php echo $link['collection_class_name'] ?>',
			'collection_table' => '<?php echo $linkTableName ?>',
			'collection_key' => '<?php echo $link['collection_key'] ?>',
			'relation_key' => '<?php echo $primaryKeyName ?>',
			'retired_column' => <?php echo $retiredColumn ?>,
			'is_retired' => <?php echo $isRetiredValue ?>,
			'is_not_retired' => <?php echo $isNotRetiredValue ?>

		),
<?php endforeach; ?>

		// Many-to-many collections
<?php foreach ($manyToManyLinks as $linkTableName => $link):

	// Join table: Set the values of retired column and set/not set values, but only if the linked table has the retired column.
	if (isset($this->tables[$linkTableName]['variables'][$this->retiredColumn])) {
		$retiredColumn = "'" . $this->retiredColumn . "'";
		$isRetiredValue = "'" . $this->isRetiredValue . "'";
		$isNotRetiredValue = "'" . $this->isNotRetiredValue . "'";
	} else {
		$retiredColumn = 'null';
		$isRetiredValue = 'null';
		$isNotRetiredValue = 'null';
	}
	// Related Table: Set the values of retired column and set/not set values, but only if the linked table has the retired column.
	if (isset($this->tables[$link['join_table_name']]['variables'][$this->retiredColumn])) {
		$collectionTableRetiredColumn = "'" . $this->retiredColumn . "'";
		$collectionTableIsRetiredValue = "'" . $this->isRetiredValue . "'";
		$collectionTableIsNotRetiredValue = "'" . $this->isNotRetiredValue . "'";
	} else {
		$collectionTableRetiredColumn = 'null';
		$collectionTableIsRetiredValue = 'null';
		$collectionTableIsNotRetiredValue = 'null';
	}

?>
		'<?php echo $link['object_camel_name'] ?>' => array(
			'element_class' => '<?php echo $link['element_class_name'] ?>',
			'collection_class' => '<?php echo $link['collection_class_name'] ?>',
			'collection_table' => '<?php echo $linkTableName ?>',
			'collection_key' => '<?php echo $link['collection_key'] ?>',
			'join_table' => '<?php echo $link['join_table_name'] ?>',
			'join_table_attr' => array(
				'retired_column' => <?php echo $collectionTableRetiredColumn ?>,
				'is_retired' => <?php echo $collectionTableIsRetiredValue ?>,
				'is_not_retired' => <?php echo $collectionTableIsNotRetiredValue ?>

			),
			'join_primary_key' => '<?php echo $link['join_table_primary_key'] ?>',
			'relation_key' => '<?php echo $primaryKeyName ?>',
			'retired_column' => <?php echo $retiredColumn ?>,
			'is_retired' => <?php echo $isRetiredValue ?>,
			'is_not_retired' => <?php echo $isNotRetiredValue ?>

		),
<?php endforeach; ?>
	);
*/ ?>

<?php
$selectSql = array();
$innerJoins = array();
foreach ($table->getHasOneRelationships() as $hasOne) {
	if (!$hasOne->isKeyNullable($hasOne->getLocalKey())) {
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
		foreach ($hasOne->getRefTable->getColumns() as $columnName => $refColumn) {
			$selectSql[] = '`' . $refTableName . '`.`' . $columnName . '` AS `' . $refTableName . '.' . $columnName . '`';
		}
	}
}
if (count($joins) > 0) {
?>
	public function getLoadSqlWithoutWhere() {
		return '
			SELECT
				`<?php echo $tableName ?>`.*
				, <?php echo implode("\n\t\t\t\t, ", $selectSql) . "\n" ?>
			FROM
				`<?php echo $dbName ?>`.`<?php echo $tableName ?>`
<?php foreach ($innerJoins as $joinTable => $joinCriteria) { ?>
				INNER JOIN <?php echo $joinTable . "\n" ?>
					ON <?php echo implode("\n\t\t\t\t\tAND ", $joinCriteria) . "\n" ?>
<? } ?>
		';
	}
<?php
}
?>


	// Generated attribute accessors (getters and setters)

<?php
	foreach ($table->getColumns() as $columnName => $column):
		$titleCase = $this->config->getTitleCase($columnName);
?>
	public function get<?php echo $titleCase ?>() {
		return $this->getField('<?php echo $columnName ?>');
	}
	
	public function set<?php echo $titleCase ?>($value) {
		$this->setField('<?php echo $columnName ?>', $value);
	}

<?php endforeach; ?>

	// Generated one-to-one accessors (loaders, getters, and setters)

<?php
	foreach ($table->getHasOneRelationships() as $hasOne):
		$objectTitleCase = $this->getTitleCase($hasOne->getRefObjectName());
		$objectClassName = $this->config->getStarterObjectClassName($hasOne->getRefTable());
		$localVarName = $this->getCamelCase($hasOne->getRefTableName()); //'object';
		if ($localVarName == 'this') {
			// avoid naming conflict
			$localVarName = 'object';
		}
		$localKey = $hasOne->getLocalKey();
?>
	public function load<?php echo $objectTitleCase ?>_Object() {
		$<?php echo $localVarName ?> = new <?php echo $objectClassName ?>(array(
<?php foreach ($hasOne->getRefKey() as $key => $column): ?>
			'<?php echo $column->getColumnName() ?>' => $this->get<?php echo $this->config->getTitleCase($localKey[$key]->getColumName()) ?>(),
<?php endforeach; ?>
		));
		if (!$<?php echo $localVarName ?>->isLoaded()) {
			$<?php echo $localVarName ?> = null;
		}
		$this->set<?php echo $objectTitleCase ?>_Object($<?php echo $localVarName ?>);
	}
	
	public function get<?php echo $objectTitleCase ?>_Object() {
		return $this->getObject('<?php echo $hasOne->getRefObjectName() ?>');
	}
	
	public function set<?php echo $objectTitleCase ?>_Object($<?php echo $localVarName ?>) {
		$this->setObject('<?php echo $hasOne->getRefObjectName() ?>', $<?php echo $localVarName ?>);
	}

<?php endforeach; ?>

	// Generated one-to-many collection loaders, getters, setters, adders, and removers

<?php
	foreach ($table->getHasManyRelationships() as $hasMany):
		$objectTitleCase = $this->getTitleCase($hasMany->getRefObjectName());
		$objectClassName = $this->config->getStarterObjectClassName($hasMany->getRefTable());
		$collectionClassName = $this->config->getStarterCollectionClassName($hasMany->getRefTable());
		$localKey = $hasMany->getLocalKey();
?>
	public function load<?php echo $objectTitleCase ?>_Collection() {
		
		// What are we collecting?
		$elementClassName = '<?php echo $objectClassName ?>';
		
		// Get the base SQL (so we can use the same SELECT and JOINs as the element class)
		$element = new $elementClassName();
		$sql = $element->getLoadSqlWithoutWhere();
		
		// What criteria are we using?
		$criteria = array(
<?php foreach ($hasMany->getRefKey() as $key => $column): ?>
			'<?php echo $column->getColumName() ?>' => $this->get<?php echo $this->config->getTitleCase($localKey[$key]->getColumName()) ?>(),
<?php endforeach; ?>
		);
		$sql .= ' ' . $this->db->generateWhere($criteria);
		
		// Construct and populate the collection
		$collection = new <?php echo $collectionClassName ?>();
		$collection->setCollector($this, CoughCollection::ONE_TO_MANY); // TODO: Anthony: Remove type? The collection object should not need this info anymore... (and, FYI, all collections are one-to-many -- it only differs when we provide "direct access" to table2 without having to go through a join table.)
		$collection->populateCollection($elementClassName, $sql);
		$this->set<?php echo $objectTitleCase ?>_Collection($collection);
	}

	public function get<?php echo $objectTitleCase ?>_Collection() {
		return $this->getCollection('<?php echo $hasMany->getRefObjectName() ?>');
	}

	public function set<?php echo $objectTitleCase ?>_Collection($<?php echo $localVarName ?>) {
		$this->setCollection('<?php echo $hasMany->getRefObjectName() ?>', $<?php echo $localVarName ?>);
	}
	
	public function add<?php echo $objectTitleCase ?>($objectOrId) {
		return $this->get<?php echo $objectTitleCase ?>_Collection()->add($objectOrId);
	}
	
	public function remove<?php echo $objectTitleCase ?>($objectOrId) {
		$removedObject = $this->get<?php echo $objectTitleCase ?>_Collection()->remove($objectOrId);
<?php foreach ($hasMany->getRefKey() as $key => $column): ?>
		$removedObject->set<?php echo $this->config->getTitleCase($column->getColumnName()) ?>(null);		
<?php endforeach; ?>
		return $removedObject;
	}

<?php endforeach; ?>

	// Generated many-to-many collection loaders, getters, setters, adders, and removers

<?php
	foreach ($table->getHabtmRelationships() as $habtm):
		$objectTitleCase = $this->getTitleCase($habtm->getRefObjectName());
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
		$sql .= ' ' . $this->db->generateWhere($criteria);
		
		// Construct and populate the collection
		$collection = new <?php echo $collectionClassName ?>();
		$collection->setCollector($this, CoughCollection::MANY_TO_MANY); // TODO: Anthony: Remove type? The collection object should not need this info anymore... (and, FYI, all collections are one-to-many -- it only differs when we provide "direct access" to table2 without having to go through a join table.)
		$collection->populateCollection($elementClassName, $sql);
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

<?php endforeach; ?>
}
<?php
		echo("\n?>\n");
		
		// Add the class
		$class = new CoughClass();
		$class->setContents(ob_get_clean());
		$class->setIsStarterClass(false);
		$class->setIsCollectionClass(false);
		$class->setClassName($className);
		$this->addGeneratedClass($class);
	}

	/**
	 * Generates the base collection class
	 *
	 * @return string the generated PHP code
	 * @author Anthony Bush
	 **/
	protected function generateBaseCollection() {
		
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
	protected $dbName = '<?php echo $dbName ?>';
	protected $elementClassName = '<?php echo $starterObjectClassName ?>';
	
	protected function defineCollectionSQL() {
		$elementClassName = $this->elementClassName;
		$element = new $elementClassName();
		$this->collectionSql = $element->getLoadSqlWithoutWhere();
	}
	
	protected function defineElementClassName() {
		$this->elementClassName = '<?php echo $table->getTableName() ?>';
	}
	
}
<?php
		echo("\n?>\n");
		
		// Add the class
		$class = new CoughClass();
		$class->setContents(ob_get_clean());
		$class->setIsStarterClass(false);
		$class->setIsCollectionClass(true);
		$class->setClassName($className);
		$this->addGeneratedClass($class);
	}

	/**
	 * Generates the starter object class
	 *
	 * @return string the generated PHP code
	 * @author Anthony Bush
	 **/
	protected function generateStarterObject() {
		
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		
		$starterObjectClassName     = $this->config->getStarterObjectClassName($table);
		$starterCollectionClassName = $this->config->getStarterCollectionClassName($table);
		$baseObjectClassName        = $this->config->getBaseObjectClassName($table);
		$baseCollectionClassName    = $this->config->getBaseCollectionClassName($table);
		
		$phpdocTags = $this->generatePhpdocTags($table);
		$phpdocTags[] = '@see ' . $baseObjectClassName . ', CoughObject';
		
		$extensionClassName = $this->config->getCollectionExtensionClassName($table);
		
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
		if ($object->isLoaded()) {
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
	 * @return <?php echo $starterObjectClassName ?>
	 **/
	public static function constructByFields($hash) {
		return new <?php echo $starterObjectClassName ?>($hash);
	}
	
}
<?php
		echo("\n?>\n");
		
		// Add the class
		$class = new CoughClass();
		$class->setContents(ob_get_clean());
		$class->setIsStarterClass(true);
		$class->setIsCollectionClass(false);
		$class->setClassName($className);
		$this->addGeneratedClass($class);
	}

	/**
	 * Generates the starter collection class
	 *
	 * @return string the generated PHP code
	 * @author Anthony Bush
	 **/
	protected function generateStarterCollection() {
		
		$dbName = $table->getDatabase()->getDatabaseName();
		$tableName = $table->getTableName();
		
		$starterObjectClassName     = $this->config->getStarterObjectClassName($table);
		$starterCollectionClassName = $this->config->getStarterCollectionClassName($table);
		$baseObjectClassName        = $this->config->getBaseObjectClassName($table);
		$baseCollectionClassName    = $this->config->getBaseCollectionClassName($table);
		
		$phpdocTags = $this->generatePhpdocTags($table);
		$phpdocTags[] = '@see ' . $baseCollectionClassName . ', CoughCollection';
		
		$extensionClassName = $this->config->getCollectionExtensionClassName($table);
		
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
		echo("\n?>\n");
		
		// Add the class
		$class = new CoughClass();
		$class->setContents(ob_get_clean());
		$class->setIsStarterClass(true);
		$class->setIsCollectionClass(true);
		$class->setClassName($className);
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