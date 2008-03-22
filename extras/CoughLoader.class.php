<?php

/**
 * CoughLoader simplifies inclusion of Cough objects and collections.
 * 
 * NOTE: If you haven't tried the Autoloader, we recommend you try it first
 * because it greatly simplifies development -- not just for cough -- but in
 * general.
 * 
 * CoughLoader Usage follows:
 * 
 * Configure CoughLoader
 * 
 *     include_once('coughphp/extras/CoughLoader.class.php');
 *     CoughLoader::addModelPath(APP_PATH . 'models/');
 *     CoughLoader::addModelPath(SHARED_PATH . 'models/');
 * 
 * Using it to load all classes for a given database:
 * 
 *     CoughLoader::loadCoughClasses('dbName');
 * 
 * Using it to load one class:
 * 
 *     CoughLoader::loadCoughClasses('dbName', 'ClassName');
 *  
 * Using it to load multiple classes:
 * 
 *     CoughLoader::loadCoughClasses('dbName', array('ClassName1', 'ClassName2'));
 * 
 * Note the class name must be the concrete object class name.  For example, if
 * the `Author_Collection` class is needed, specify `Author`.  It will load the
 * generated classes and then the concrete classes for both `Author` and
 * `Author_Colleciton`.
 *
 * @package cough
 * @author Anthony Bush
 **/
class CoughLoader {
	/**
	 * An array of model paths.
	 * 
	 * Example: array('/app/models/', '/shared/models/');
	 * 
	 * @var array $modelPaths
	 */
	protected static $modelPaths = array();
	public static $generatedClassFolder = 'generated/';
	public static $subClassFolder       = 'concrete/';
	public static $generatedObjectSuffix     = '_Generated.class.php';
	public static $generatedCollectionSuffix = '_Collection_Generated.class.php';
	public static $subObjectSuffix           = '.class.php';
	public static $subCollectionSuffix       = '_Collection.class.php';
	
	/**
	 * Set the model path(s).
	 * 
	 * @param string $paths - the paths to the model include directory. Include a trailing directory separator.
	 **/
	public static function setModelPaths(array $paths) {
		self::$modelPaths = $paths;
	}
	
	/**
	 * Add to the model paths.
	 *
	 * @param string $path - the path to the model include directory. Include a trailing directory separator.
	 **/
	public static function addModelPath($path) {
		if ( ! in_array($path, self::$modelPaths)) {
			self::$modelPaths[] = $path;
		}
	}
	
	/**
	 * Get the model paths that CoughLoader is currently using.
	 *
	 * @return array
	 * @author Anthony Bush
	 **/
	public static function getModelPaths() {
		return self::$modelPaths;
	}
	
	/**
	 * Loads Cough classes in the correct order for the given database and base class names.
	 * 
	 * A base class name would be like 'Order'. The loader will look for and load
	 * all of the following class names (if they exist):
	 * 
	 *     Order_Generated
	 *     Order_Collection_Generated
	 *     Order
	 *     Order_Collection
	 * 
	 * This works for non-generated classes as well, so long as your custom non-generated
	 * class includes the base class that it extends. The cough loader will look for
	 * generated classes, but it won't die if it doesn't find them.
	 * 
	 * @param string $dbName - name of database to load Cough classes for
	 * @param mixed $classNames - string of single class name or array of class names to load.
	 *        If not specified, all the classes for the given database are loaded.
	 * @return void
	 **/
	public static function loadCoughClasses($dbName, $classNames = null) {
		
		// Print useful message if no modelPaths have been set
		if ( ! is_array(self::$modelPaths) || empty(self::$modelPaths)) {
			echo('<strong>Error:</strong> No model paths have been set in CoughLoader. Use CoughLoader::setModelPaths() to configure this.<br />');
			return false;
		}
		
		// Find the models path for the specified database
		$foundPath = false;
		foreach (self::$modelPaths as $modelPath) {
			$dbModelsPath = $modelPath . $dbName . '/';
			if (file_exists($dbModelsPath) && is_readable($dbModelsPath)) {
				$foundPath = true;
				break;
			}
		}
		if ( ! $foundPath) {
			return false;
		}
		
		// We found the models path, so include the requested classes
		$generatedClassPath = $dbModelsPath . self::$generatedClassFolder;
		$subClassPath = $dbModelsPath . self::$subClassFolder;
		
		if (is_null($classNames)) {
			
			// Load all files in the models generated class directory, followed by the models sub class directory
			
			self::loadAllClassesInPath($generatedClassPath);
			self::loadAllClassesInPath($subClassPath);
			return true;
			
		} else {
			
			// Load classes for all the specified table names
			
			if ( ! is_array($classNames)) {
				$classNames = array($classNames);
			}
			
			$foundPath = false;
			foreach ($classNames as $className) {
				$includeFiles = array(
					$generatedClassPath . $className . self::$generatedObjectSuffix,
					$generatedClassPath . $className . self::$generatedCollectionSuffix,
					$subClassPath . $className . self::$subObjectSuffix,
					$subClassPath . $className . self::$subCollectionSuffix
				);
				foreach ($includeFiles as $includeFile) {
					if (file_exists($includeFile) && is_readable($includeFile)) {
						include_once($includeFile);
						$foundPath = true;
					}
				}
			}
			return $foundPath;
		}
	}
	
	/**
	 * Loads all the classes in the given path that end in ".class.php", non-recusively.
	 * 
	 * @param string $path - full path name that contains the classes wanting to be loaded.
	 * @return void
	 **/
	public static function loadAllClassesInPath($path) {
		$classDir = dir($path);

		// Include all files ending in '.class.php' in the specified folder (non-recursive).
		while ($f = $classDir->read()) {
			if (strpos($f, '.') !== 0 && strstr($f, '.class.php') == '.class.php') {
				include_once($path . $f);
			}
		}
		
	}
}
?>
