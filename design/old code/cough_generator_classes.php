<?php
/**
 * Cough Generator Classes
 * 
 * Basically works like this: The generator uses the database (or some other
 * schema definition, maybe XML in the future)
 * 
 * The generator looks at a schema, uses your configuration, and outputs a
 * bunch of Model/ORM classes.
 * 
 * A schema can be:
 * 
 *     - Database (e.g. MySQL database)
 *     - XML (e.g. Propel's XML definitions... TODO)
 *     - etc.
 * 
 * Configuration can customize:
 * 
 *     - Output paths
 *     - Class names
 *     - Method names
 *     - Items to ignore
 *     - Items to generate differently from the rest
 *     - and more.
 *
 * @author Anthony Bush
 * @version $Id: CoughGenerator.class.php,v 1.25 2007/04/17 19:17:00 awbush Exp $
 * @copyright Academic Superstore, 11 July, 2006
 * @package shared
 **/


/**
 * Responsibility: To generate CRUD classes for a given database using
 * CoughObject and CoughCollecion as the base classes.
 * 
 * CoughGenerator is used for generating the basics of using Cough. The
 * generated code directly extends CoughObject and CoughCollection and 
 * uses the naming namespace_Table_Generated and
 * namespace_Table_Collection_Generated.
 * 
 * Update 2006-07-27:
 * Cough is now looking at foreign key relationships for both one-to-many
 * and many-to-many relationships. By convention, foreign keys are detected
 * by them having '_id' in the name AND them not being a primary key. This
 * condition of detecting what is considered a foreign key can be changed by
 * changing `$this->idSuffix` and/or the whole function `isForeignKey`.
 * 
 * TODO:
 * Detect name collisions. E.g. With a bad database design we might have:
 *     product2os table with product_id and os_id
 *     AND a product table
 *     AND an os table
 * Now, if we have the same keyname as wasy in product2os for either of the
 * product of os tables, then we would have a name collision. That is, if
 * we also had product.os_id or os.product_id. The desired behavior for this
 * condition would be to ignore the individual ones and only generate the
 * class for the '2' table. I'm not so sure we care about this.
 * 
 * Features for next version:
 * 
 * - Handling of parent/child relationships
 * 
 * - Handling of table2table (= same name on both sides), currently
 *   ignores, and maybe that is what we want always?
 * 
 * - Handling of table2table_join (our current naming schema breaks
 *   down here, but there could be a way to detect all this stuff regardless
 *   of what the table name is, eh?). Currently it checks that the 'table' on
 *   either side of the '2' actually exists, so in the case of breakdown that
 *   table simply gets no recognition.
 *
 * Example usage:
 * $cg = new CoughGenerator(new DatabaseDefinitions($configArray));
 * if ($cg->generate()) {
 *     echo 'Success generating ' . $dbName . "\n";
 * } else {
 *     echo 'Failure generating ' . $dbName . "\n";
 *     echo 'Errors reported by CoughGenerator:' . "\n";
 *     echo implode("<br>\n" . $cg->getErrorMessages()) . "<br>\n";
 * }
 * 
 * Try to use different steps:
 * 1. $cg->generateCodeInMemory()
 * 2. $cg->compareCodeToOutputDir()
 *    2a. $cg->getRemovedFiles()
 *    2b. $cg->getModifiedFiles()
 *    2c. $cg->getAddedFiles()
 *    2d. CvsHelper::generateRemove($cg->getRemovedFiles()), and so on. (not responsibility of CoughGenerator, but maybe the web panel will use some helper classes to make good use of the CoughGenerator output.)
 * 3. $cg->writeGeneratedCodeToFile()
 * 
 *
 * Example usage: (Old)
 * $cg = new CoughGenerator($dbHost, $dbUser, $dbPass);
 * if ($cg->generate($dbName, $classPrefix)) {
 *     echo 'Success generating ' . $dbName . "\n";
 * } else {
 *     echo 'Failure generating ' . $dbName . "\n";
 *     echo 'Errors reported by CoughGenerator:' . "\n";
 *     echo implode("<br>\n" . $cg->getErrorMessages()) . "<br>\n";
 * }
 * 
 * @package shared
 * @author Anthony Bush
 **/
class CoughGenerator {
	// Constants
	const DEBUG = false;
	
	// CoughGenerator version (used in documentation next to "@author CoughGenerator::functionName()")
	protected $generatorVersion = '0.3';
	
	protected $config = array(
		'phpDoc' => array(
			'author' => 'CoughGenerator',
			'package' => 'shared',
			// 'copyright' => 'Academic Superstore',
		),
		'paths' => array(
			'generated_classes' => dirname(__FILE__) . '/generated_classes/',
			'starter_classes' => dirname(__FILE__) . '/starter_classes/',
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
			// 'match_table_name_prefixes' => array('cust_', 'wfl_', 'baof_'),
			// Additionally, you can strip table prefixes from the generated class names (note that you might run into naming conflicts though.)
			// 'strip_table_name_prefixes' => array('cust_', 'wfl_', 'baof_'),
			// You can ignore tables all together, too:
			'ignore_tables_matching_regex' => '/(_bak$)|(^bak_)|(^temp_)/',
		),
		'field_settings' => array(
			'id_regex' => '/^(.*)_id$/',
			'retired_column' => 'is_retired',
			'is_retired_value' => '1',
			'is_not_retired_value' => '0', // TODO: deprecate this. Have the code use != is_retired_value
		),

		// All databases will be scanned unless specified in the 'databases' parameter.
		'dsn' => array(
			'host' => 'crump',
			'user' => 'root',
			'pass' => '3v3ry0n3l@rp5!',
			'port' => 3306,

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
			// 					'retired_column' => 'status',
			// 					'is_retired_value' => 'cancelled',
			// 				),
			// 			),
			// 		),
			// 	),
			// ),
		),
	);
	
	// Database Server Connection Information (passed to __construct, except dbLink)
	protected $dbServer;
	protected $dbUsername;
	protected $dbPassword;
	protected $dbLink;
	
	// Database information to generate code for (pased to generate function)
	protected $dbName;
	protected $classPrefix;
	
	// For documentation (set these in init)
	protected $author;
	protected $package;
	protected $copyright;
	
	// Location and naming of classes/files (set these in init)
	protected $generatedBaseFolder;
	protected $generatedStarterFolder;
	protected $baseObjectSuffix;
	protected $baseCollectionSuffix;
	protected $starterObjectSuffix;
	protected $starterCollectionSuffix;
	protected $fileExt;
	
	// Misc settings
	protected $idSuffix;
	protected $retiredColumn;
	protected $isRetiredValue;
	protected $isNotRetiredValue;
	protected $tableNamePrefixes = array(); // an array of prefixes, e.g. 'baof_', 'wfl_', etc. so that we can do better join table detection
	
	/**
	 * Specifies whether or not to remove all php files from the generated
	 * directories before writing new ones. This ensures that any files that
	 * are no longer relevant are removed.
 	 * 
	 * @var boolean
	 **/
	protected $cleanBeforeGen = true;
	
	// Error reporting
	protected $checkedSuccessfully;
	protected $errorMessages;
	protected $warnings;
	
	/**
	 * Retrieved information about a table
	 * 
	 * tables == array(
	 *     'table_name' => array(
	 *         'variables' => array(...)
	 *         'primary_key' => ...
	 *         'primary_keys' => ...
	 *         'class_name' => ...
	 *     )
	 * )
	 *
	 * @var array
	 **/
	protected $tables;
	protected $tableName; // name of the current table we are on
	protected $table;     // reference to current table in the tables array
	
	/**
	 * Contents of all the generate files
	 * 
	 * generateFiles = array(
	 *     'file_name' => contents
	 * )
	 * 
	 * After this works, we may want to have stuff just write directly
	 * to the file rather than store everything in memory. Be aware though
	 * that we should still store a list of fileNames that we are saving
	 * so that we can still detect generation conflicts.
	 * 
	 * @var array
	 **/
	protected $generatedFiles;
	
	/**
	 * Stores an array of files that existed in the generation directories before running the generator
	 * 
	 * Format of [directory name] => array of "*.class.php" files in the directory.
	 *
	 * @var array
	 **/
	protected $filesBeforeGenerating = array();
	const FILE_NOCHANGE = 0;
	const FILE_ADDED = 1;
	const FILE_REMOVED = 2;
	const FILE_MODIFIED = 3;
	
	protected $removedFiles = array();
	protected $addedFiles = array();
	protected $modifiedFiles = array();

	public function __construct() {
		
	}
	
	/**
	 * This is the main entry point for the CoughGenerator.
	 * 
	 * It takes care of making sure any remaining initialization
	 * takes place and starts the database scanning / class generation
	 * process.
	 *
	 * @param string $dbName the database to scan
	 * @param string $classPrefix an optional prefix (really namespace) to avoid class name collisions
	 * @return boolean true on success, false on failure
	 * @author Anthony Bush
	 **/
	public function generate($dbName, $classPrefix = '') {
		$this->dbName = $dbName;
		$this->classPrefix = $classPrefix; // probably should rename to 'nameSpace'
		$this->init();
		
		$this->tables = array();
		$this->errorMessages = array();

		$this->ensureDirectoryExists($this->generatedBaseFolder);
		$this->ensureDirectoryExists($this->generatedStarterFolder);
		
		if ($this->check()) {
			if (self::DEBUG) {
				echo '<h2>Tables Found in ' . $this->dbName . ' (' . count($this->tables) . ')</h2>';
				$this->jamTables();
			}
			$this->generateClassesForDatabase();
			
			//$this->jamTables();
			//$this->jamTablesWithForeignKeys();
			if (self::DEBUG) {
				echo '<h2>Generated Files / Classes (' . count($this->generatedFiles) . ')</h2>';
				$this->jamFiles();
			}
			// For now, let debug mode still generate the files...
			if (false && self::DEBUG) {
				$this->errorMessages[] = 'DEBUG MODE ENABLED, so will always fail.';
				return false;
			} else {
				
				// Save the directory listings so we can print messages about files that were added or removed
				$this->saveDirectoryListing($this->generatedBaseFolder);
				$this->saveDirectoryListing($this->generatedStarterFolder);
				$this->recordFileChanges();
				
				// We are about to save new files, if cleanBeforeGen is set, let's remove existing files first.
				if ($this->cleanBeforeGen) {
					$this->cleanDirectory($this->generatedBaseFolder);
					$this->cleanDirectory($this->generatedStarterFolder);
				}
				
				
				return $this->save();
			}
		} else {
			return false;
		}
	}
	
	/**
	 * Sets the configuration for documentation and location/naming of classes.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function init() {
		// Documentation
		$this->author = 'CoughGenerator ' . $this->generatorVersion;
		$this->package = 'shared';
		$this->copyright = 'Academic Superstore';
		
		// Location and naming of classes and files
		// TODO: switch to the new paths:
		// $this->generatedBaseFolder
		// 	= '../../shared/classes/'
		// 	. $this->dbName
		// 	. '/models/generated/';
		// 
		// $this->generatedStarterFolder
		// 	= '../../shared/classes/'
		// 	. $this->dbName
		// 	. '/models/extended/';

		$this->generatedBaseFolder
			= '../../shared/models/'
			. $this->dbName
			. '/generated/generated_classes/';

		$this->generatedStarterFolder
			= '../../shared/models/'
			. $this->dbName
			. '/generated/starter_classes/';
		
		$this->baseObjectSuffix = '_Generated';
		$this->baseCollectionSuffix = '_Collection_Generated';
		$this->starterObjectSuffix = '';
		$this->starterCollectionSuffix = '_Collection';
		$this->fileExt = '.class.php';
		
		// Misc Settings
		$this->idSuffix = '_id';
		$this->retiredColumn = 'is_retired';
		$this->isRetiredValue = '1';
		$this->isNotRetiredValue = '0';
		
		/**
		 * Sets the suffix to use in the defineCollections method of an Object class.
		 * It is only used if the object has a many-to-many relationship with another
		 * object and would be used like the following if you set it to 's':
		 *
		 *    $this->otherObjects = new namespace_OtherObject_Collection();
		 *                      ^
		 *  It is used here ----|
		 *
		 * @var string
		 **/
		$this->objectDefineCollectionSuffix = '_Collection';
		
		/**
		 * DEPRECATED, override the getPrimaryKeyFromForeignKey($foreignKey) function
		 * instead.
		 *
		 * Sets the prefixes that should be stripped from a foreign key before looking for
		 * it as the primary key of another table.
		 * 
		 * For example, if you name a foreign key as 'default_os_id' and you mean for the
		 * primary key to be named 'os_id' in other table, then add an entry to the array
		 * for this setting as 'default_'.
		 *
		 * @var array of strings	
		 **/
		//$this->otmForeignKeyPrefixes = array('primary_', 'default_');
	}
	
	
	
	####################
	# SAVING FUNCTIONS #
	####################
	
	
	
	/**
	 * Saves the give class to file or memory.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function saveClass($name, $usage, $type, $contents) {
		$fileName = $this->getFileName($name, $usage, $type);
		
		if (isset($this->generatedFiles[$fileName])) {
			$this->jamCollision($fileName, $this->generatedFiles[$fileName], $contents);
		}
		
		// Save all files to memory first
		$this->generatedFiles[$fileName] = $contents;
		
		// Or just save it now (more efficient, but then can only jam stuff on-the-fly)
		//$this->writeToFile($fileName, $contents);
	}
	
	/**
	 * Get the file name that should be used.
	 *
	 * @param $name name of class that will go inside the file
	 * @param $classUage either "base" or "starter"
	 * @param $type either "object" or "collection"
	 * @return string the full file name to be used including either a relative or absolute path.
	 * @author Anthony Bush
	 **/
	protected function getFileName($name, $usage, $type) {
		$fileName = '';
		
		if ($usage == 'base') {
			$fileName .= $this->generatedBaseFolder;
		} else {
			$fileName .= $this->generatedStarterFolder;
		}
		
		$fileName .= $name;
		
		if ($type == 'object') {
			if ($usage == 'base') {
				$fileName .= $this->baseObjectSuffix;
			} else {
				$fileName .= $this->starterObjectSuffix;
			}
		} else {
			if ($usage == 'base') {
				$fileName .= $this->baseCollectionSuffix;
			} else {
				$fileName .= $this->starterCollectionSuffix;
			}
		}
		
		$fileName .= $this->fileExt;
		return $fileName;
	}
	
	/**
	 * Checks if the given directory exists, and if it doesn't, it
	 * attempts to create it.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function ensureDirectoryExists($dir) {
		if ( ! file_exists($dir)) {
			mkdir($dir, 0777, true);
		}
	}
	
	/**
	 * Removes all *.class.php files in the given directory (or whatever
	 * $this->fileExt is set to, if you changed it).
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function cleanDirectory($dir) {
		if (file_exists($dir) && is_writable($dir)) {
			exec('rm -f "' . rtrim($dir, '/') . '/"*.class.php');
			return true;
		} else {
			return false;
		}
	}
	
	protected function saveDirectoryListing($dir) {
		if (file_exists($dir) && is_readable($dir)) {
			$files = scandir($dir);
			foreach ($files as $file) {
				if (preg_match('|^[^.].*\.class\.php$|', $file)) {
					$this->filesBeforeGenerating[$dir . $file] = self::FILE_NOCHANGE;
				}
			}
		}
	}
	
	protected function recordFileChanges() {
		// Record files that were added.
		foreach (array_keys($this->generatedFiles) as $filename) {
			if ( ! isset($this->filesBeforeGenerating[$filename])) {
				$this->filesBeforeGenerating[$filename] = self::FILE_ADDED;
				$this->addedFiles[] = $filename;
			}
		}
		
		// Record files that were removed.
		foreach ($this->filesBeforeGenerating as $filename => $change) {
			if ( ! isset($this->generatedFiles[$filename])) {
				// File removed
				$this->filesBeforeGenerating[$filename] = self::FILE_REMOVED;
				$this->removedFiles[] = $filename;
			} else {
				// Check if file was modified
				if (file_exists($filename)) {
					$curFileContents = file_get_contents($filename);
					if ($curFileContents != $this->generatedFiles[$filename]) {
						$this->filesBeforeGenerating[$filename] = self::FILE_MODIFIED;
						$this->modifiedFiles[] = $filename;
					}
				}
			}
		}
	}
	
	public function getRemovedFiles() {
		return $this->removedFiles;
	}
	
	public function getAddedFiles() {
		return $this->addedFiles;
	}
	
	public function getModifiedFiles() {
		return $this->modifiedFiles;
	}
	
	/**
	 * Saves all generated files from memory to disk
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function save() {
		foreach ($this->generatedFiles as $fileName => $fileContents) {
			$this->writeToFile($fileName, $fileContents);
		}
		
		if (empty($this->errorMessages)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Puts the given contents into the given file name.
	 *
	 * @param string $fileName the file name to write to.
	 * @param string $fileContents the contents to write.
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function writeToFile($fileName, $fileContents) {
		if (file_exists($fileName)) {
			if (is_writable($fileName)) {
				$bytesWritten = file_put_contents($fileName, $fileContents);
				if ($bytesWritten == 0) {
					$this->errorMessages[] = 'Wrote zero bytes to ' . $fileName;
				}
			} else {
				$this->errorMessages[] = 'Unable to write to file (no permissions on file): ' . $fileName;
			}
		} else {
			if (is_writeable(dirname($fileName))) {
				$bytesWritten = file_put_contents($fileName, $fileContents);
				if ($bytesWritten == 0) {
					$this->errorMessages[] = 'Wrote zero bytes to ' . $fileName;
				}
			} else {
				$this->errorMessages[] = 'Unable to write to file (no permissions on directory): ' . $fileName;
			}
		}
	}
	
	
	
	###############################
	# DATABASE SCANNING FUNCTIONS #
	###############################
	
	
	
	/**
	 * check() calls the appropriate checkBlah() function that will connect to the database
	 * and retrieve information about a given database.
	 *
	 * @return boolean true on success, false on failure.
	 * @author Anthony Bush
	 **/
	protected function check() {
		try {
			/**
			 * In here we could detect the db type or use a type set in the consructor
			 * in order to call the appropriate checkBlah() function.
			 **/
			$this->checkedSuccessfully = $this->checkMySql();
		} catch (Exception $e) {
			$this->logException($e);
			$this->checkedSuccessfully = false;
		}
		return $this->checkedSuccessfully;
	}
	
	/**
	 * checkMySql() connects to the database on the server and calls `checkMySqlTable()`
	 * and `generate()` for each table in the database. The generated code is put into
	 * $this->fileContents.
	 *
	 * @return boolean true on success, throws error on failure.
	 * @throws Exception
	 * @author Anthony Bush
	 **/
	protected function checkMySql() {
		// Connect to the server
		$this->dbLink = mysql_connect($this->dbServer, $this->dbUsername, $this->dbPassword);
		if (!$this->dbLink) {
			throw new Exception('Could not connect: ' . mysql_error());
		}
		
		// Select a database
		$db_selected = mysql_select_db($this->dbName, $this->dbLink);
		if (!$db_selected) {
			throw new Exception("Can't select database: " . mysql_error());
		}
		
		// Get list of tables
		$sql = 'SHOW TABLES';
		$result = mysql_query($sql, $this->dbLink);
		if (!$result) {
			throw new Exception('Invalid query: ' . mysql_error($this->dbLink));
		}
		$tableNames = array();
		while ($record = mysql_fetch_array($result, MYSQL_NUM)) {
			$tableNames[] = $record[0];
		}
		
		// Generate the file contents (call checkMySqlTable/generate for each table)
		foreach ($tableNames as $this->tableName) {
			$this->checkMySqlTable();
		}
		
		// Close the connection
		mysql_close($this->dbLink);
		
		return true;
	}
	
	/**
	 * checkMySqlTable() uses the existing DB connection to check the given table name
	 * and bring its information into scope.
	 *
	 * @param string $tableName the table to get info for.
	 * @return void
	 * @throws Exception
	 * @author Anthony Bush
	 **/
	protected function checkMySqlTable() {
		if ($this->shouldIgnoreTable()) {
			return;
		}
		
		// Run a query
		$sql = 'DESCRIBE ' . $this->tableName;
		$result = mysql_query($sql, $this->dbLink);
		if (!$result) {
			throw new Exception('Invalid query: ' . mysql_error($this->dbLink));
		}
		
		// Process the results
		$titleCaseName = $this->getTitleCase($this->tableName);
		$camelCaseName = $this->getCamelCase($this->tableName);
		$className = $this->classPrefix . $titleCaseName;
		$variables = array();
		$primaryKey = null;
		$primaryKeys = array();
		$foriegnKeys = array();
		while ($record = mysql_fetch_assoc($result)) {
			/**
			 * We have the following information on a MySQL 4 server:
			 * 
			 *     [Field] => db_column_name
			 *     [Type] => datetime/varchar(40)/etc.
			 *     [Null] => empty or 'YES'
			 *     [Key] => empty or 'PRI' or ...
			 *     [Default] => 0000-00-00 00:00:00
			 *     [Extra] => empty or 'auto_increment' or ...
			 *
			 * TODO: Add code to retrieve the comments field on a MySQL 5 server.
			 * Put those comments in $variable['comments'] and use it in the `defineColumns`
			 * portion of the generated code.
			 **/
			
/* DEBUG: Print what MySQL returns
			echo '<hr>';
			echo '<h6>Record for ' . $this->tableName . '</h6>';
			echo '<pre>';
			print_r($record);
			echo '</pre>';
*/
			$variable = array();
			$dbColumnName = $record['Field'];
			
			// db_column_name -> DbColumnName
			$variable['title_case_name'] = $this->getTitleCase($dbColumnName);
			// db_column_name -> dbColumnName
			$variable['camel_case_name']= $this->getCamelCase($dbColumnName);
			// Keep track of whether or not null is allowed.
			if (strtolower($record['Null']) == 'yes') {
				$variable['is_null_allowed'] = true;
			} else {
				$variable['is_null_allowed'] = false;
			}
			// Set default values (value from MySQL will be PHP null, false, true, or a string)
			$variable['default_value'] = $record['Default'];

			// Some other stuff we aren't using right now... (from Wayne's Generate code)
			$variable['typefield'] = $record['Type'];
			$typefield = preg_split('/[\(\)]/',$record['Type']);
			$variable['type'] = $typefield[0];
			if (count($typefield) > 1) {
				$variable['size'] = $typefield[1];
			}

			// Don't store the primary key(s) in the variables array, it's "special".
			if ($record['Key'] == 'PRI') {
				$primaryKeys[$dbColumnName] = $variable;
				// Backward compatibility for legacy code that hasn't bee updated yet:
				// (yes, it is overwriting the primary key if we encounter more than one; probably the first one was the correct one, but this is how it was done in legacy...)
				$variable['db_column_name'] = $dbColumnName;
				$primaryKey = $variable;
			} else {
				// If it is a foreign key, store it in the variables array, but also keep track of it
				if ($this->isForeignKey($dbColumnName, $record['Key'])) {
					$foriegnKeys[] = $dbColumnName;
				}	
				$variables[$dbColumnName] = $variable;
			}
		}
		
/* DEBUG: Do we have any tables with zero or a bunch of primary keys?
		if (count($primaryKeys) > 1) {
			die($this->tableName . ' has more than one primary key:<pre>' . print_r($primaryKeys) . '</pre>');
		} else if (count($primaryKeys) == 0) {
			die('No primary keys for ' . $this->tableName);
		}
*/
		if (is_null($primaryKey)) {
			$this->warnings[] = 'No primary key on `' . $this->tableName . '`.';
		}
		
		// Save it
		$this->tables[$this->tableName] = array(
			'class_name' => $className,
			'title_case_name' => $titleCaseName,
			'camel_case_name' => $camelCaseName,
			'primary_key'  => $primaryKey,
			'primary_keys' => $primaryKeys,
			'foreign_keys' => $foriegnKeys,
			'variables' => $variables,
		);
		
	}
	
	
	
	########################
	# GENERATION FUNCTIONS #
	########################
	
	
	
	/**
	 * Iterates through all tables and passes control to the table
	 * -> class generator.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function generateClassesForDatabase() {
		$this->processTables();
		$tableNames = array_keys($this->tables);
		foreach ($tableNames as $this->tableName) {
			$this->table =& $this->tables[$this->tableName];
			$this->generateClassesForTable();
		}
	}
	
	/**
	 * Generates all classes associated for the currently
	 * selected table.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function generateClassesForTable() {

		try {
			if ($this->shouldGenerateObject()) {
				$this->doGenerateObject();

				if ($this->shouldGenerateCollection()) {
					$this->doGenerateCollection();
				}
			}
		} catch (Exception $e) {
			$this->logException($e);
		}
		
	}
	
	protected function shouldGenerateObject() {
		return $this->hasPrimaryKey() && ! $this->isJoinTable() && ! $this->shouldIgnoreTable();
	}
	
	protected function shouldGenerateCollection() {
		// Besides needing whatever requirements the object has, there aren't any other.
		return true;
	}
	
	protected function doGenerateObject() {
		$className = $this->table['class_name'];
		$this->saveClass($className, 'base',    'object', $this->generateBaseObject());
		$this->saveClass($className, 'starter', 'object', $this->generateStarterObject());
	}

	protected function doGenerateCollection() {
		$className = $this->table['class_name'];
		$this->saveClass($className, 'base',    'collection', $this->generateBaseCollection());
		$this->saveClass($className, 'starter', 'collection', $this->generateStarterCollection());
	}
	
	/**
	 * Generates the base object class
	 *
	 * @return string the generated PHP code
	 * @author Anthony Bush
	 **/
	protected function generateBaseObject() {
		
		// Setup some shortcuts
		$table      =& $this->table;
		$tableLinks =& $this->table['table_links'];
		$oneToOneLinks   =& $this->table['table_links']['oto'];
		$oneToManyLinks  =& $this->table['table_links']['otm'];
		$manyToManyLinks =& $this->table['table_links']['mtm'];
		$primaryKeyName = $this->getPrimaryKeyName();
		
		ob_start();
		echo("<?php\n\n");
		?>
/**
 * This is the base class for <?=$table['class_name'] . $this->starterObjectSuffix?>.
 *
 * @package <?=$this->package . "\n"?>
 * @author <?=$this->author . "\n"?>
 * @author CoughGenerator::generateBaseObject() <?=$this->generatorVersion . "\n"?>
 * @copyright <?=$this->copyright . "\n"?>
 * @see <?=$table['class_name'] . $this->starterObjectSuffix . ", CoughObject" . "\n"?>
 **/
abstract class <?=$table['class_name'] . $this->baseObjectSuffix?> extends CoughObject {
	
	// Generated one-to-one attributes
	
<?php foreach ($oneToOneLinks as $link): ?>
	protected $<?=$link['object_camel_name']?>;
<?php endforeach; ?>
	
	// Generated one-to-many collection attributes
	
<?php
	foreach ($oneToManyLinks as $linkTableName => $link):
		// many-to-many collection takes precedence.
		// TODO: Cough: Make sure this is the correct way to handle this. Make sure to fix the other two loops below.
		if (isset($manyToManyLinks[$linkTableName])) {
			$this->warnings[] = 'Duplicate collection name `' . $link['object_camel_name'] . '` in class `' . $table['class_name'] . '`. Ignoring the one-to-many collection in favor of many-to-many collection.';
			continue;
		}
?>
	protected $<?=$link['object_camel_name']?>;
<?php endforeach; ?>

	// Generated many-to-many collection attributes
	
<?php foreach ($manyToManyLinks as $link): ?>
	protected $<?=$link['object_camel_name']?>;
<?php endforeach; ?>

	// Generated definition functions

	protected function defineDBName() {
		$this->dbName = '<?=$this->dbName?>';
	}
	
	protected function defineTableName() {
		$this->tableName = '<?=$this->tableName?>';
	}
	
	protected function defineKeyColumn() {
		$this->keyColumn = '<?=$primaryKeyName?>';
	}
	
	protected function defineNameColumn() {
		$this->nameColumn = '<?=$this->getNameColumn()?>';
	}
	
	protected function defineColumns() {
		$this->columns = array(
			'<?=$table['primary_key']['db_column_name']?>' => array(
				'db_column_name' => '<?= $table['primary_key']['db_column_name'] ?>',
				'is_null_allowed' => <?= $this->getStringFromPhpValue($table['primary_key']['is_null_allowed']) ?>,
				'default_value' => <?= $this->getStringFromPhpValue($table['primary_key']['default_value']) . "\n" ?>
			),
<?php foreach ($table['variables'] as $dbColumnName => $attributes): ?>
			'<?= $dbColumnName ?>' => array(
				'db_column_name' => '<?= $dbColumnName ?>',
				'is_null_allowed' => <?= $this->getStringFromPhpValue($attributes['is_null_allowed']) ?>,
				'default_value' => <?= $this->getStringFromPhpValue($attributes['default_value']) . "\n" ?>
			),
<?php endforeach; ?>
		);
	}
	
	protected function defineObjects() {
		$this->objects = array(
<?php foreach ($oneToOneLinks as $link): ?>
			'<?=$link['object_camel_name']?>' => array(
				'class_name' => '<?=$link['element_class_name']?>',
				'get_id_method' => 'get<?=$link['foreign_key_title_name']?>'
			),
<?php endforeach; ?>
		);
	}
	
	protected function defineCollections() {
		$this->collections = array(
			
			// One-to-many collections
			
<?php
	foreach ($oneToManyLinks as $linkTableName => $link):
		// many-to-many collection takes precedence.
		if (isset($manyToManyLinks[$linkTableName])) {
			continue;
		}
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
			'<?=$link['object_camel_name']?>' => array(
				'element_class' => '<?=$link['element_class_name']?>',
				'collection_class' => '<?=$link['collection_class_name']?>',
				'collection_table' => '<?=$linkTableName?>',
				'collection_key' => '<?=$link['collection_key']?>',
				'relation_key' => '<?=$primaryKeyName?>',
				'retired_column' => <?=$retiredColumn?>,
				'is_retired' => <?=$isRetiredValue?>,
				'is_not_retired' => <?=$isNotRetiredValue?>

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
			'<?=$link['object_camel_name']?>' => array(
				'element_class' => '<?=$link['element_class_name']?>',
				'collection_class' => '<?=$link['collection_class_name']?>',
				'collection_table' => '<?=$linkTableName?>',
				'collection_key' => '<?=$link['collection_key']?>',
				'join_table' => '<?=$link['join_table_name']?>',
				'join_table_attr' => array(
					'retired_column' => <?=$collectionTableRetiredColumn?>,
					'is_retired' => <?=$collectionTableIsRetiredValue?>,
					'is_not_retired' => <?=$collectionTableIsNotRetiredValue?>

				),
				'join_primary_key' => '<?=$link['join_table_primary_key']?>',
				'relation_key' => '<?=$primaryKeyName?>',
				'retired_column' => <?=$retiredColumn?>,
				'is_retired' => <?=$isRetiredValue?>,
				'is_not_retired' => <?=$isNotRetiredValue?>

			),
<?php endforeach; ?>
		);
	}
<?php /*
	protected function finishConstruction() {
<?php foreach ($oneToOneLinks as $link): ?>
		$this-><?=$link['object_camel_name']?> = new <?=$link['element_class_name']?>($this->get<?=$link['foreign_key_title_name']?>());
<?php endforeach; ?>
		
		$this->initCollections();
	}
*/ ?>

	// Generated attribute accessors (getters and setters)

<?php foreach ($table['variables'] as $dbColumnName => $attributes): ?>
	public function get<?=$attributes['title_case_name']?>() {
		return $this->getField('<?=$dbColumnName?>');
	}
	public function set<?=$attributes['title_case_name']?>($value) {
		$this->setField('<?=$dbColumnName?>', $value);
	}
	
<?php endforeach; ?>
	
	// Generated one-to-one attribute checkers and getters (no setters)
	
<?php foreach ($oneToOneLinks as $linkName => $link): ?>
	public function check<?=$link['object_title_name']?>_Object() {
		$this->checkObject('<?=$link['object_camel_name']?>');
	}
	public function get<?=$link['object_title_name']?>_Object() {
		return $this-><?=$link['object_camel_name']?>;
	}
	public function coag<?=$link['object_title_name']?>_Object() {
		return $this->checkOnceAndGetObject('<?=$link['object_camel_name']?>');
	}
<?php if ( ! isset($table['variables'][$linkName])): ?>
	// DEPRECATED, use check<?=$link['object_title_name']?>_Object() instead.
	public function check<?=$link['object_title_name']?>() {
		$this->checkObject('<?=$link['object_camel_name']?>');
	}
	// DEPRECATED, use get<?=$link['object_title_name']?>_Object() instead.
	public function get<?=$link['object_title_name']?>() {
		return $this-><?=$link['object_camel_name']?>;
	}
	// DEPRECATED, use coag<?=$link['object_title_name']?>_Object() instead.
	public function coag<?=$link['object_title_name']?>() {
		return $this->checkOnceAndGetObject('<?=$link['object_camel_name']?>');
	}
<?php endif; ?>
	
<?php endforeach; ?>
	
	// Generated one-to-many collection checkers, getters, setters, adders, and removers
	
<?php
	foreach ($oneToManyLinks as $linkTableName => $link):
		// many-to-many collection takes precedence.
		if (isset($manyToManyLinks[$linkTableName])) {
			continue;
		}
?>
	public function check<?=$link['object_title_name']?>() {
		return $this->checkOneToManyCollection('<?=$link['object_camel_name']?>');
	}
	
	public function get<?=$link['object_title_name']?>() {
		return $this->getCollection('<?=$link['object_camel_name']?>');
	}
	
	public function coag<?=$link['object_title_name']?>() {
		return $this->checkOnceAndGetCollection('<?=$link['object_camel_name']?>');
	}
	
	public function set<?=$link['object_title_name']?>($objectsOrIDs = array()) {
		$this->setCollection('<?=$link['object_camel_name']?>', $objectsOrIDs);
	}
	
	public function add<?=$link['table_title_name']?>($objectOrID) {
		$this->addToCollection('<?=$link['object_camel_name']?>', $objectOrID);
	}
	
	public function remove<?=$link['table_title_name']?>($objectOrID) {
		$this->removeFromCollection('<?=$link['object_camel_name']?>', $objectOrID);
	}
	
<?php endforeach; ?>
	
	// Generated many-to-many collection attributes
	
<?php foreach ($manyToManyLinks as $link): ?>
	public function check<?=$link['object_title_name']?>() {
		return $this->checkManyToManyCollection('<?=$link['object_camel_name']?>');
	}

	public function get<?=$link['object_title_name']?>() {
		return $this->getCollection('<?=$link['object_camel_name']?>');
	}

	public function coag<?=$link['object_title_name']?>() {
		return $this->checkOnceAndGetCollection('<?=$link['object_camel_name']?>');
	}
	
	public function set<?=$link['object_title_name']?>($objectsOrIDs = array()) {
		$this->setCollection('<?=$link['object_camel_name']?>', $objectsOrIDs);
	}

	public function add<?=$link['table_title_name']?>($objectOrID, $joinFields = null) {
		$this->addToCollection('<?=$link['object_camel_name']?>', $objectOrID, $joinFields);
	}

	public function remove<?=$link['table_title_name']?>($objectOrID) {
		$this->removeFromCollection('<?=$link['object_camel_name']?>', $objectOrID);
	}
	
<?php endforeach; ?>
}
<?php
		echo("\n?>\n");
		return ob_get_clean();
	}
	
	/**
	 * Generates the base collection class
	 *
	 * @return string the generated PHP code
	 * @author Anthony Bush
	 **/
	protected function generateBaseCollection() {
		$table =& $this->tables[$this->tableName];
		
		if ( ! empty($this->retiredColumn) && isset($table['variables'][$this->retiredColumn])) {
			$whereClause = ' WHERE ' . $this->retiredColumn . ' = ' . $this->isNotRetiredValue;
		} else {
			$whereClause = '';
		}
		
		ob_start();
		echo("<?php\n\n");
		?>
/**
 * This is the base class for <?=$table['class_name'] . $this->starterCollectionSuffix?>.
 *
 * @package <?=$this->package . "\n"?>
 * @author <?=$this->author . "\n"?>
 * @author CoughGenerator::generateBaseCollection() <?=$this->generatorVersion . "\n"?>
 * @copyright <?=$this->copyright . "\n"?>
 * @see <?=$table['class_name'] . $this->starterCollectionSuffix . ", CoughCollection" . "\n"?>
 **/
abstract class <?=$table['class_name'] . $this->baseCollectionSuffix?> extends CoughCollection {
	protected function defineDBName() {
		$this->dbName = '<?=$this->dbName?>';
	}
	protected function defineCollectionSQL() {
		$this->collectionSQL = 'SELECT * FROM <?=$this->tableName . $whereClause?>';
	}
	protected function defineElementClassName() {
		$this->elementClassName = '<?=$table['class_name']?>';
	}
	protected function defineSpecialCriteria($specialArgs=array()) {
		// this modifies the collectionSQL based on special parameters
	}
}
<?php
		echo("\n?>\n");
		return ob_get_clean();
	}
	
	/**
	 * Generates the starter object class
	 *
	 * @return string the generated PHP code
	 * @author Anthony Bush
	 **/
	protected function generateStarterObject() {
		ob_start();
		echo("<?php\n\n");
		?>
/**
 * This is the starter class for <?=$this->table['class_name'] . $this->baseObjectSuffix?>.
 *
 * @package <?=$this->package . "\n"?>
 * @author <?=$this->author . "\n"?>
 * @author CoughGenerator::generateStarterObject() <?=$this->generatorVersion . "\n"?>
 * @copyright <?=$this->copyright . "\n"?>
 * @see <?=$this->table['class_name'] . $this->baseObjectSuffix . ", CoughObject" . "\n"?>
 **/
class <?=$this->table['class_name'] . $this->starterObjectSuffix?> extends <?=$this->table['class_name'] . $this->baseObjectSuffix?> {
	protected function defineObjects() {
		parent::defineObjects();
		
		// Add to the defined objects like so:
		
		// $this->objects['objectName'] = array(
		// 	'class_name' => 'woc_ObjectName',
		// 	'get_id_method' => 'getObjectNameID'
		// );
	}
	protected function defineCollections() {
		parent::defineCollections();
		
		// Add to the defined collections like so:
		
		// For a one-to-many collection where there is no join table:
		// $this->collections['document_Collection'] = array(
		// 	'element_class' => 'woc_Document',
		// 	'collection_class' => 'woc_Document_Collection',
		// 	'collection_table' => 'document',
		// 	'collection_key' => 'document_id',
		// 	'relation_key' => 'product_id',
		// 	'retired_column' => 'is_retired',
		// 	'is_retired' => '1',
		// 	'is_not_retired' => '0'
		// );
		
		// For a many-to-many collection where there is a join table:
		// $this->collections['subProduct_Collection'] = array(
		// 	'element_class' => 'woc_SubProduct',
		// 	'collection_class' => 'woc_SubProduct_Collection',
		// 	'collection_table' => 'product',
		// 	'collection_key' => 'product_id',
		// 	'join_table' => 'product2product',
		//	'join_table_attr' => array(
		//		'retired_column' => 'is_retired',
		//		'is_retired' => '1',
		//		'is_not_retired' => '0'
		//	),
		// 	'join_primary_key' => 'product2product_id',
		// 	'relation_key' => 'child_id',
		// 	'retired_column' => 'is_retired',
		// 	'is_retired' => '1',
		// 	'is_not_retired' => '0',
		// 	'custom_check_function' => 'checkSubProduct_Collection'
		// );
		
	}
	protected function finishConstruction() {
		parent::finishConstruction();
		
		// Un-comment this line to have all the objects instantiated and checked
		// every time *this* object is instantiated.
		// NOTE: calling superCheck also does this in addition to checking all
		// collections.
		
		// $this->checkAllObjects();
	}
}
<?php
		echo("\n?>\n");
		return ob_get_clean();
	}
	
	/**
	 * Generates the starter collection class
	 *
	 * @return string the generated PHP code
	 * @author Anthony Bush
	 **/
	protected function generateStarterCollection() {
		ob_start();
		echo("<?php\n\n");
		?>
/**
 * This is the starter class for <?=$this->table['class_name'] . $this->baseCollectionSuffix?>.
 *
 * @package <?=$this->package . "\n"?>
 * @author <?=$this->author . "\n"?>
 * @author CoughGenerator::generateStarterCollection() <?=$this->generatorVersion . "\n"?>
 * @copyright <?=$this->copyright . "\n"?>
 * @see <?=$this->table['class_name'] . $this->baseCollectionSuffix . ", CoughCollection" . "\n"?>
 **/
class <?=$this->table['class_name'] . $this->starterCollectionSuffix?> extends <?=$this->table['class_name'] . $this->baseCollectionSuffix?> {
}
<?php
		echo("\n?>\n");
		return ob_get_clean();
	}
	
	
	
	#########################
	# GENERATION COMPONENTS #
	#########################
	
	
	
	/**
	 * Returns a string containing the PHP code for the protected function defineCollections()
	 * It will return an empty string if no defineCollections() is needed so you just print
	 * the results without worry.
	 *
	 * @return string PHP code to insert into the generated class (the code is the whole method)
	 * @author Anthony Bush
	 **/
	/* DEPRECATED
	protected function getDefineCollections() {
		// Example:
		// $this->functions = new woc_Function_Collection;
		// $this->Oss = new woc_Os_Collection;
		// $this->subjects = new woc_Subject_Collection;
		// $this->productCollections = new woc_ProductCollection_Collection;
		// $this->vendorProducts = new woc_VendorProduct_Collection;		
		$joinTables = $this->getJoinTableObjectNames();
		$collections = array();
		foreach ($joinTables as $joinTableName => $tableName) {
			if (isset($this->tables[$tableName])) {
				$attributeName
					= $this->tables[$tableName]['camel_case_name']
					. $this->objectDefineCollectionSuffix;
				
				$collections[$tableName]
					= "\t\t"
					. '$this->'
					. $attributeName
					. ' = new '
					. $this->tables[$tableName]['class_name']
					. $this->starterCollectionSuffix
					. '();'
					. "\n";
				
			}
		}
		
		if (empty($collections)) {
			return '';
		} else {
			return "\tprotected function defineCollections() {\n"
				. "\t\t// " . $this->tableName . "s can belong to more than one or have more than one:\n"
				. implode('', $collections)
				. "\t}\n";
		}
	}
	*/
	
	/**
	 * DEPRECATE?
	protected function getInnerJoinCheckFunction($className, $tableNameOne, $tableNameTwo) {
		$fkOne = $tableNameOne . $this->idSuffix;
		$fkTwo = $tableNameTwo . $this->idSuffix;
		$elementClassName = $this->tables[$tableNameTwo]['class_name'];
		ob_start();
		echo "\n";
		?>
		protected function check<?=$this->tables[$tableNameTwo]['title_case_name']?>() {
			$sql = '
				SELECT <?=$tableNameTwo ."\n"?>.*
				FROM <?=$tableNameOne . "\n"?>
				INNER JOIN <?=$tableNameTwo?> ON <?=$this->tableName . '.' . $fkTwo?> = <?=$tableNameTwo . '.' . $fkTwo . "\n"?>
				WHERE <?=$this->tableName . '.' . $fkOne?> = '.$this->get<?=$this->getTitleCase($fkOne)?>();

			$this->populateCollection( , '<?=$elementClassName?>, );
		}
		<?php
		return ob_get_clean();
	}
	*/
	
	/**
	 * Render the check functions for the object class for the currently selected table.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	/* DEPRECATED
	protected function renderCheckFunctions() {
		$joinTables = $this->getJoinTableObjectNames();
		foreach ($joinTables as $joinTableName => $tableName) {
			if (isset($this->tables[$tableName])) {
				$attributeName = $this->tables[$tableName]['camel_case_name']
					. $this->objectDefineCollectionSuffix;
				$attributeTitleName = $this->tables[$tableName]['title_case_name']
					. $this->objectDefineCollectionSuffix;
				$elementClassName = $this->tables[$tableName]['class_name'];
				$foreignKey = $this->table['primary_key']['db_column_name'];
				?>
	
	public function check<?=$attributeTitleName?>() {
		$sql = '
			SELECT *
			FROM <?=$joinTableName."\n"?>
			WHERE <?=$foreignKey?> = ' . $this->getKeyID();
		
		$this->populateCollection('<?=$attributeName?>', '<?=$elementClassName?>', $sql);
	}
				<?php
			}
		}
	}
	*/
	
	/**
	 * Gets the `finishConstruction` method for the object class for the currently
	 * selected table
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function getFinishConstruction() {
		/**
		 * Generate entries based on one-to-many relationships where one of
		 * an attribute can have many of the current table object and one of
		 * object can only have one of the attribute.
		 **/ 
		
		// Generate getFinishConstruction information, but only if they have foreign keys.
		$singles = array();
		if ( ! empty($this->table['foreign_keys'])) {
			foreach ($this->table['foreign_keys'] as $foreignKey) {
				// e.g. item_id or primary_os_id
				// item_id => $this->item = new namespace_Item($this->getItemID());
				// primary_os_id => $this->primaryOs = new namespace_Os($this->getPrimaryOsID());
				
				$primaryKeyName = $this->getPrimaryKeyFromForeignKey($foreignKey);
				$attributeName = $this->getCamelCase($this->getKeyNameWithoutSuffix($foreignKey));
				$fkTableName = $this->getTableWhosePrimaryKeyIs($primaryKeyName);
				if (empty($fkTableName)) {
					continue;
				}
				$singles[$attributeName]
					= "\t\t"
					. '$this->'
					. $attributeName
					. ' = new '
					. $this->tables[$fkTableName]['class_name']
					. $this->starterObjectSuffix
					. '($this->get'
					. $this->table['variables'][$foreignKey]['title_case_name']
					. '());'
					. "\n";
			}
		}
		
		if (empty($singles)) {
			return '';
		} else {
			return "\tprotected function finishConstruction() {\n"
				. "\t\t// " . $this->tableName . "s have either only one, a primary, or a default...\n"
				. implode('', $singles)
				. "\t}\n";
		}
	}
	
	
	
	#######################
	# ARTILLERY FUNCTIONS #
	#######################
	
	/**
	 * Caches all the information about each table and puts it into the tables
	 * array under `table_links`.
	 * 
	 * Would be a good idea to call this one after pulling all the info from
	 * the database via the `check` function
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function processTables() {
		foreach (array_keys($this->tables) as $tableName) {
			$this->tables[$tableName]['table_links'] = $this->processTable($tableName);
		}
	}
	
	/**
	 * Returns the table links for the given table (or currently selected table
	 * if none given).
	 **/
	protected function processTable($tableName = '') {
		
		if (empty($tableName)) {
			$tableName = $this->tableName;
		}
		$table = $this->tables[$tableName];
		
		$primaryKeyName = $this->getPrimaryKeyName($tableName);
		
		$tableLinks = array(
			'oto' => array(),
			'otm' => array(),
			'mtm' => array(),
		);
		
		// Get one-to-one objects
		
		if ( ! empty($table['foreign_keys'])) {
			foreach ($table['foreign_keys'] as $foreignKey) {
				// e.g. item_id or primary_os_id
				// item_id => $this->item = new namespace_Item($this->getItemID());
				// primary_os_id => $this->primaryOs = new namespace_Os($this->getPrimaryOsID());
				
				$fkTableName = $this->getTableWhosePrimaryKeyIs($this->getPrimaryKeyFromForeignKey($foreignKey));
				if (empty($fkTableName) || $this->shouldIgnoreTable($fkTableName)) {
					continue;
				}
				
				$keyName = $this->getKeyNameWithoutSuffix($foreignKey);
				$attributeTitleName = $this->getTitleCase($keyName);
				$attributeName = $this->getCamelCase($keyName);
				
				$tableLinks['oto'][$fkTableName] = array(
					'object_title_name' => $attributeTitleName,
					'object_camel_name' => $attributeName,
					'element_class_name' => $this->tables[$fkTableName]['class_name'] . $this->starterObjectSuffix,
					'foreign_key_title_name' => $table['variables'][$foreignKey]['title_case_name'],
				);
				
			}
		}
		
		// Get one-to-many collections
		
		foreach ($this->tables as $foreignTableName => $foreignTable) {
			if ($this->isJoinTable($foreignTableName) || $this->shouldIgnoreTable($foreignTableName)) {
				continue;
			}
			if ( ! empty($foreignTable['foreign_keys'])) {
				foreach ($foreignTable['foreign_keys'] as $foreignKey) {
					
					// Did we find a relationship?
					if ($primaryKeyName == $foreignKey) {
						
						// Get attributes
						
						$attributeName
							= $this->tables[$foreignTableName]['camel_case_name']
							. $this->objectDefineCollectionSuffix;
						
						$attributeTitleName
							= $this->tables[$foreignTableName]['title_case_name']
							. $this->objectDefineCollectionSuffix;

						$elementClassName = $this->tables[$foreignTableName]['class_name'];
						
						// Add to one-to-many collections
						
						$tableLinks['otm'][$foreignTableName] = array(
							'table_title_name' => $this->tables[$foreignTableName]['title_case_name'],
							'table_camel_name' => $this->tables[$foreignTableName]['camel_case_name'],
							'object_title_name' => $attributeTitleName,
							'object_camel_name' => $attributeName,
							'element_class_name' => $elementClassName . $this->starterObjectSuffix,
							'collection_class_name' => $elementClassName . $this->starterCollectionSuffix,
							'collection_key' => $this->getPrimaryKeyName($foreignTableName),
						);
						
					}
				}
			}
		}
		
		// Get many-to-many collections
		
		$joinTables = $this->getJoinTableObjectNames($tableName);
		foreach ($joinTables as $joinTableName => $otherTableName) {
			if (isset($this->tables[$otherTableName]) && ! $this->shouldIgnoreTable($otherTableName)) {
				
				// Get attributes
				
				$attributeName
					= $this->tables[$otherTableName]['camel_case_name']
					. $this->objectDefineCollectionSuffix;

				$attributeTitleName
					= $this->tables[$otherTableName]['title_case_name']
					. $this->objectDefineCollectionSuffix;

				$elementClassName = $this->tables[$otherTableName]['class_name'];
				
				// Add to many-to-many collections
				
				$tableLinks['mtm'][$otherTableName] = array(
					'table_title_name' => $this->tables[$otherTableName]['title_case_name'],
					'table_camel_name' => $this->tables[$otherTableName]['camel_case_name'],
					'object_title_name' => $attributeTitleName,
					'object_camel_name' => $attributeName,
					'element_class_name' => $elementClassName . $this->starterObjectSuffix,
					'collection_class_name' => $elementClassName . $this->starterCollectionSuffix,
					'collection_key' => $this->getPrimaryKeyName($otherTableName),
					'join_table_name' => $joinTableName,
					'join_table_primary_key' => $this->getPrimaryKeyName($joinTableName),
				);
				
			}
		}
		
		return $tableLinks;
	}
	
	/**
	 * Returns the name column of the specified table (or currently selected
	 * table if not given).
	 *
	 * @return string - the name column
	 * @author Anthony Bush
	 **/
	public function getNameColumn($tableName = '') {
		if (empty($tableName)) {
			$tableName = $this->tableName;
		}
		$table = $this->tables[$tableName];
		
		// Try finding exactly tablename_name, e.g. for `product` table look for `product_name`
		if (isset($table['variables'][$tableName . '_name'])) {
			return $tableName . '_name';
		}
		
		// Try finding exactly `name`
		if (isset($table['variables']['name'])) {
			return 'name';
		}

		// Try finding exactly tablename, e.g. `product`
		if (isset($table['variables'][$tableName])) {
			return $tableName;
		}
		
		// Try looking for tablename_name, e.g. for `product` table look for `product_name`
		// but also match field if there is other stuff in it, e.g. `funny_product_name`
		foreach (array_keys($table['variables']) as $dbColumnName) {
			$pos = strpos($dbColumnName, $tableName . '_name');
			if ($pos !== false) {
				return $dbColumnName;
			}
		}
		
		// Try again looking for just `_name` anywhere in the column name.
		foreach (array_keys($table['variables']) as $dbColumnName) {
			$pos = strpos($dbColumnName, '_name');
			if ($pos !== false) {
				return $dbColumnName;
			}
		}
		
		// Just return the primary_id column
		return $this->getPrimaryKeyName();
	}
	
	/**
	 * Returns whether or not the currently selected table has
	 * a primary key
	 *
	 * @return boolean - true if table has a primary key, false if not
	 * @author Anthony Bush
	 **/
	protected function hasPrimaryKey() {
		return ! empty($this->table['primary_keys']);
	}
	
	/**
	 * Tests whether or not the give table name (or currently selected table
 	 * if not given) is a join table or not.
	 *
	 * @return boolean - true if table is a join table, false if not.
	 * @author Anthony Bush
	 **/
	protected function isJoinTable($tableName = '') {
		if (empty($tableName)) {
			return (strpos($this->tableName, '2') !== false);
		} else {
			return (strpos($tableName, '2') !== false);
		}
	}
	
	/**
	 * Returns whether or not the currently selected table (or given table) is
	 * in development.
	 * 
	 * @param string $tableName - the table name to check the development status of
	 * @return boolean - true if table is in development, false if not.
	 * @author Anthony Bush
	 **/
	protected function shouldIgnoreTable($tableName = '') {
		if (empty($tableName)) {
			$tableName = $this->tableName;
		}
		
		if (substr($tableName, 0, 5) == 'elig_') {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * NOTE: I don't think this is used anywhere...
	 * Gets an array of table names that are join tables for the currently
	 * selected table.
	 *
	 * @return array of strings - the table names
	 * @author Anthony Bush
	 **/
	protected function getJoinTables() {
		$joinTables = array();
		foreach ($this->tables as $tableName => $table) {
			if ($this->isJoinTable($tableName)) {
				$joinTables[] = $tableName;
			}
		}
		return $joinTables;
	}
	
	/**
	 * Returns the table names on either side of the '2',
	 * but only if they aren't equal. If they are equal or
	 * there is no '2', then false is returned
	 *
	 * @return mixed false or a two element array.
	 * @author Anthony Bush
	 **/
	protected function getJoinTableNames($tableName = '') {
		if (empty($tableName)) {
			$tableName = $this->tableName;
		}
		// Must have '2' in the name, and the names on either side of the 2 must not be equal.
		$tableNamePieces = explode('2', $tableName);
		if (count($tableNamePieces) == 2) {
			if ($tableNamePieces[0] == $tableNamePieces[1]) {
				return false;
			} else {
				return $tableNamePieces;
			}
		}
		return false;
	}
	
	/**
	 * Returns all related object names for the current table.
	 * 
	 * @return array of table2Name => table
	 * @author Anthony Bush
	 **/
	protected function getJoinTableObjectNames($tableNameToGetFor = '') {
		if (empty($tableNameToGetFor)) {
			$tableNameToGetFor = $this->tableName;
		}
		
		$joinTableObjectNames = array();
		if ( ! $this->isJoinTable($tableNameToGetFor)) {
			foreach ($this->tables as $tableName => $table) {
				$tableNamePieces = explode('2', $tableName);
				if (count($tableNamePieces) == 2) {
					if ($tableNamePieces[0] == $tableNamePieces[1]) {
						continue;
					} else if ($tableNamePieces[0] == $tableNameToGetFor || $tableNameToGetFor == $this->getTableNameRegardlessOfPrefix($tableNamePieces[0])) {
						$otherTableName = $this->getTableNameRegardlessOfPrefix($tableNamePieces[1]);
						if ($otherTableName) {
							$joinTableObjectNames[$tableName] = $otherTableName;
						}
					} else if ($tableNamePieces[1] == $tableNameToGetFor || $tableNameToGetFor == $this->getTableNameRegardlessOfPrefix($tableNamePieces[1])) {
						$otherTableName = $this->getTableNameRegardlessOfPrefix($tableNamePieces[0]);
						if ($otherTableName) {
							$joinTableObjectNames[$tableName] = $otherTableName;
						}
					}
				} // if join table
			} // for each table
		} // is not join table
		return $joinTableObjectNames;
	}
	
	protected function getTableNameRegardlessOfPrefix($tableName) {
		if (isset($this->tables[$tableName])) {
			return $tableName;
		} else {
			// If a prefix exists, remove it and try that table too, but no other tables after that.
			foreach ($this->tableNamePrefixes as $prefix) {
				if (substr($tableName, 0, strlen($prefix)) == $prefix) {
					$tableNameWithoutPrefix = substr($tableName, strlen($prefix) - 1);
					if (isset($this->tables[$tableNameWithoutPrefix])) {
						return $tableNameWithoutPrefix;
					} else {
						return null;
					}
				}
			}
			
			// There is no prefix, so try iterating through all available prefixes until we find a match
			foreach ($this->tableNamePrefixes as $prefix) {
				$tableWithPrefix = $prefix . $tableName;
				if (isset($this->tables[$tableWithPrefix])) {
					return $tableWithPrefix;
				}
			}
			
		}
		return null;
	}
	
	/**
	 * Returns whether or not the given dbColumnName is is a foreign key.
	 *
	 * @return boolean true if given column name is a foreign key, false if not.
	 * @author Anthony Bush
	 **/
	protected function isForeignKey($dbColumnName, $dbKeyValue) {
		return (substr($dbColumnName, -strlen($this->idSuffix)) == $this->idSuffix
			&& (strpos($dbColumnName, '2') === false));
	}
	
	/**
	 * Gets the given key name without the idSuffix on the end.
	 * 
	 * If there is no idSuffix on the end, it just returns what you
	 * gave it.
	 *
	 * @return string the key name without suffix
	 * @author Anthony Bush
	 **/
	protected function getKeyNameWithoutSuffix($keyName) {
		if (substr($keyName, -strlen($this->idSuffix)) == $this->idSuffix) {
			return substr($keyName, 0, -strlen($this->idSuffix));
		}
		return $keyName;
	}
	
	/**
	 * Override this function if you need to specify different foreign key ->
	 * primary key relationships
	 *
	 * @return string primary key to look for
	 * @author Anthony Bush
	 **/
	protected function getPrimaryKeyFromForeignKey($foreignKey) {
		if (substr($foreignKey, 0, 8) == 'default_') {
			return substr($foreignKey, 8);
		} else if (substr($foreignKey, 0, 8) == 'primary_') {
			return substr($foreignKey, 8);
		}
		// For now, don't do any special parent/child stuff... TODO: Add parent/child handling
		// else if (substr($foreignKey, 0, 7) == 'parent_') {
		// 	return $this->tableName . $this->idSuffix;
		// } else if (substr($foreignKey, 0, 6) == 'child_') {
		// 	return $this->tableName . $this->idSuffix;
		// }
		else {
			return $foreignKey;
		}
	}
	
	/**
	 * Gets the table name that has the given key as a primary key.
	 *
	 * Try giving it a foreign key. It will give you back the table name
	 * that has it has the primary key.
	 *
	 * @return string the table name
	 * @author Anthony Bush
	 **/
	protected function getTableWhosePrimaryKeyIs($keyName) {
		foreach ($this->tables as $tableName => $table) {
			if ($table['primary_key']['db_column_name'] == $keyName) {
				return $tableName;
			}
		}
		return '';
	}
	
	/**
	 * Gets the primary key db_column_name for the currently selected table
	 * or, if provided, the given table name.
	 *
	 * @return string the primary key name
	 * @author Anthony Bush
	 **/
	protected function getPrimaryKeyName($tableName = '') {
		if (empty($tableName)) {
			return $this->table['primary_key']['db_column_name'];
		} else {
			return $this->tables[$tableName]['primary_key']['db_column_name'];
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
	private function getTitleCase($value) {
		$value = str_replace('_', ' ', $value);
		$value = ucwords($value);
		
		// Special Tom naming convention
		if (substr($value, -3) == ' Id') {
			$value[strlen($value) - 1] = 'D';
		}
		
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
	private function getCamelCase($value) {
		$value = $this->getTitleCase($value);
		$value[0] = strtolower($value[0]);
		return $value;
	}
	
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
	
	
	###################
	# DEBUG FUNCTIONS #
	###################
	
	
	
	/**
	 * A DEBUG function that outputs all tables and their contents
	 * in a showHide format.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function jamTables() {
		foreach ($this->tables as $tableName => $table) {
			echo '<a href="javascript:void(showHide(\'' . $tableName . '\'))">' . $tableName . '</a><br />';
			echo '<div id="' . $tableName . '" style="display: none">';
			echo '<pre>';
			echo htmlentities(print_r($table, true));
			echo '</pre>';
			echo '</div>';
		}
	}
	
	/**
	 * A DEBUG function that outputs the tables that have foreign keys
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function jamTablesWithForeignKeys() {
		foreach ($this->tables as $this->tableName => $table) {
			if ( ! empty($table['foreign_keys'])) {
				echo '<h3>' . $this->tableName . ' has Foreign Keys</h3>';
				echo '<pre>';
				print_r($table['foreign_keys']);
				echo '</pre>';
			}
		}
	}
	
	/**
	 * A DEBUG function used to output all the files / classes that
	 * have been generated in a showHide format.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function jamFiles() {
		foreach ($this->generatedFiles as $fileName => $fileContents) {
			$elementID = substr($fileName, strrpos($fileName, '/'));
			echo '<a href="javascript:void(showHide(\'' . $elementID . '\'))">' . $elementID . '</a><br />';
			echo '<div id="' . $elementID . '" style="display: none">';
			$this->jamFile($fileContents);
			echo '</div>';
		}
	}
	
	/**
	 * Safely renders given contents as HTML output
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function jamFile($contents) {
		echo '<pre>';
		echo htmlentities($contents);
		echo '</pre>';
	}
	
	/**
	 * A DEBUG function for output two files side by side.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function jamCollision($fileName, $contents1, $contents2) {
		?>
		<table cellspacing="0" cellpadding="5" border="1" class="collision">
			<tr>
				<th colspan="2">
					A file name collision occurred for:<br />
					<?=$fileName?>
				</th>
			</tr>
			<tr valign="top">
				<td><?php $this->jamFile($contents1)?></td>
				<td><?php $this->jamFile($contents2)?></td>
			</tr>
		</table>
		<?php
	}
	
	/**
	 * A DEBUG function used for displaying JavaScript to handle
	 * the showHide capability of other debug functions.
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function renderJavaScript() {
		?>
		<script type="text/javascript" language="javascript" charset="utf-8">
		// <![CDATA[
			function showHide(elementId) {
				e = document.getElementById(elementId);
				if (e.style.display == 'none') {
					e.style.display = 'block';
				} else {
					e.style.display = 'none';
				}
			}
		// ]]>
		</script>
		<?
	}
	
	/**
	 * A DEBUG function used for styling the output of
	 * other debug functions
	 *
	 * @return void
	 * @author Anthony Bush
	 **/
	protected function renderCSS() {
		?>
		<style type="text/css" media="screen">
			h2 {
			}
			pre {
				background: #eee;
				border: 1px solid #333;
				padding: 5px;
				overflow: auto;
			}
			.collision {
				margin: 1em 0;
			}
			.collision th {
				background: red;
				color: white;
			}
		</style>
		<?
	}

}

?>
