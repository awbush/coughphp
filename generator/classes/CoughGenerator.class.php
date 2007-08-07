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
		
		$starterObjectClassName = $this->config->getStarterObjectClassName($table);
		$starterCollectionClassName = $this->config->getStarterCollectionClassName($table);
		$baseObjectClassName = $this->config->getBaseObjectClassName($table);
		$baseCollectionClassName = $this->config->getBaseCollectionClassName($table);
		
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
 * This is the base class for <?= $starterObjectClassName ?>.
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
		'<?= $columnName ?>' => array(
			'db_column_name' => '<?= $columnName ?>',
			'is_null_allowed' => <?= $this->getStringFromPhpValue($column->isNullAllowed()) ?>,
			'default_value' => <?= $this->getStringFromPhpValue($column->getDefaultValue()) . "\n" ?>
		),
<?php endforeach; ?>
	);

	protected $objectDefinitions = array(
<?php foreach ($table->getHasOneRelationships() as $hasOne): ?>
		'<?php echo $hasOne->getRefTableName() ?>' => array(
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

	// Generated one-to-one accessors

<?php foreach ($table->getHasOneRelationships() as $hasOne): ?>
	public function load<?php echo $this->getTitleCase($hasOne->getRefObjectName()) ?>_Object() {
		$this->_loadObject('<?php echo $hasOne->getRefObjectName() ?>');
	}
	
	public function get<?php echo $this->getTitleCase($hasOne->getRefObjectName()) ?>_Object() {
		return $this->getObject('<?php echo $hasOne->getRefObjectName() ?>');
	}

<?php endforeach; ?>

	// Generated one-to-many collection loaders, getters, setters, adders, and removers

<?php
	foreach ($oneToManyLinks as $linkTableName => $link):
		// many-to-many collection takes precedence.
		if (isset($manyToManyLinks[$linkTableName])) {
			continue;
		}
?>
	public function load<?php echo $link['object_title_name'] ?>() {
		return $this->loadOneToManyCollection('<?php echo $link['object_camel_name'] ?>');
	}

	public function get<?php echo $link['object_title_name'] ?>() {
		return $this->getCollection('<?php echo $link['object_camel_name'] ?>');
	}

	public function coag<?php echo $link['object_title_name'] ?>() {
		return $this->loadOnceAndGetCollection('<?php echo $link['object_camel_name'] ?>');
	}

	public function set<?php echo $link['object_title_name'] ?>($objectsOrIDs = array()) {
		$this->setCollection('<?php echo $link['object_camel_name'] ?>', $objectsOrIDs);
	}

	public function add<?php echo $link['table_title_name'] ?>($objectOrID) {
		$this->addToCollection('<?php echo $link['object_camel_name'] ?>', $objectOrID);
	}

	public function remove<?php echo $link['table_title_name'] ?>($objectOrID) {
		$this->removeFromCollection('<?php echo $link['object_camel_name'] ?>', $objectOrID);
	}

<?php endforeach; ?>

	// Generated many-to-many collection attributes

<?php foreach ($manyToManyLinks as $link): ?>
	public function load<?php echo $link['object_title_name'] ?>() {
		return $this->loadManyToManyCollection('<?php echo $link['object_camel_name'] ?>');
	}

	public function get<?php echo $link['object_title_name'] ?>() {
		return $this->getCollection('<?php echo $link['object_camel_name'] ?>');
	}

	public function coag<?php echo $link['object_title_name'] ?>() {
		return $this->loadOnceAndGetCollection('<?php echo $link['object_camel_name'] ?>');
	}

	public function set<?php echo $link['object_title_name'] ?>($objectsOrIDs = array()) {
		$this->setCollection('<?php echo $link['object_camel_name'] ?>', $objectsOrIDs);
	}

	public function add<?php echo $link['table_title_name'] ?>($objectOrID, $joinFields = null) {
		$this->addToCollection('<?php echo $link['object_camel_name'] ?>', $objectOrID, $joinFields);
	}

	public function remove<?php echo $link['table_title_name'] ?>($objectOrID) {
		$this->removeFromCollection('<?php echo $link['object_camel_name'] ?>', $objectOrID);
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
		
		$starterObjectClassName = $this->config->getStarterObjectClassName($table);
		$starterCollectionClassName = $this->config->getStarterCollectionClassName($table);
		$baseObjectClassName = $this->config->getBaseObjectClassName($table);
		$baseCollectionClassName = $this->config->getBaseCollectionClassName($table);
		
		$phpdocTags = $this->generatePhpdocTags($table);
		$phpdocTags[] = '@see ' . $starterCollectionClassName . ', CoughCollection';
		
		$extensionClassName = $this->config->getCollectionExtensionClassName($table);
		
		
		// $table =& $this->tables[$this->tableName];
		// 
		// if ( ! empty($this->retiredColumn) && isset($table['variables'][$this->retiredColumn])) {
		// 	$whereClause = ' WHERE ' . $this->retiredColumn . ' = ' . $this->isNotRetiredValue;
		// } else {
		// 	$whereClause = '';
		// }

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
		$this->collectionSql = $element->getLoadSqlWithoutWhere() . '<?php echo $whereClause ?>';
	}
	
	protected function defineElementClassName() {
		$this->elementClassName = '<?php echo $table->getTableName() ?>';
	}
	
	protected function defineSpecialCriteria($specialArgs=array()) {
		// this modifies the collectionSQL based on special parameters
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