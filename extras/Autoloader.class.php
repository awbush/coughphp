<?php

/**
 * Autoloader is a class scanner with caching.
 * 
 * Sample Usage:
 * 
 *     Autoloader::addClassPath('/path/to/classes/');
 *     Autoloader::setCacheFilePath('/path/to/class_path_cache.php');
 *     Autoloader::excludeFolderNamesMatchingRegex('/^CVS|starter_classes|\..*$/');
 *     function __autoload($className) {
 *         Autoloader::loadClass($className);
 *     }
 * 
 * @package default
 * @author Anthony Bush
 **/
class Autoloader {
	protected static $classPaths = array();
	protected static $classFileSuffix = '.class.php';
	protected static $cacheFilePath = null;
	protected static $cachedPaths = null;
	protected static $excludeFolderNames = '/^CVS|\..*$/'; // CVS directories and directories starting with a dot (.).
	
	/**
	 * Sets the paths to search in when looking for a class.
	 * 
	 * @param array $paths
	 * @return void
	 **/
	public static function setClassPaths($paths) {
		self::$classPaths = $paths;
	}
	
	/**
	 * Adds a path to search in when looking for a class.
	 * 
	 * @param string $path
	 * @return void
	 **/
	public static function addClassPath($path) {
		self::$classPaths[] = $path;
	}
	
	/**
	 * Set the full file path to the cache file to use.
	 * 
	 * Example:
	 * 
	 *     Autoloader::setCacheFilePath('/tmp/class_path_cache.txt');
	 * 
	 * @param string $path
	 * @return void
	 **/
	public static function setCacheFilePath($path) {
		self::$cacheFilePath = $path;
	}
	
	/**
	 * Sets the suffix to append to a class name in order to get a file name
	 * to look for
	 * 
	 * @param string $suffix - $className . $suffix = filename.
	 * @return void
	 **/
	public static function setClassFileSuffix($suffix) {
		self::$classFileSuffix = $suffix;
	}
	
	/**
	 * When searching the {@link $classPaths} recursively for a matching class
	 * file, folder names matching $regex will not be searched.
	 * 
	 * Example:
	 * 
	 *     Autoloader::excludeFolderNamesMatchingRegex('/^CVS|\..*$/');
	 * 
	 * @param string $regex
	 * @return void
	 **/
	public static function excludeFolderNamesMatchingRegex($regex) {
		self::$excludeFolderNames = $regex;
	}
	
	/**
	 * Returns true if the class file was found and included, false if not.
	 *
	 * @return boolean
	 **/
	public static function loadClass($className) {
		
		$filePath = self::getCachedPath($className);
		if ($filePath && file_exists($filePath)) {
			// Cached location is correct
			include($filePath);
			return true;
		}
		else {
			// Scan for file
			foreach (self::$classPaths as $path) {
				if ( ($filePath = self::searchForClassFile($className, $path)) ) {
					self::$cachedPaths[$className] = $filePath;
					self::saveCachedPaths();
					include($filePath);
					return true;
				}
			}
			
		}
		return false;
	}
	
	protected static function getCachedPath($className) {
		self::loadCachedPaths();
		if (isset(self::$cachedPaths[$className])) {
			return self::$cachedPaths[$className];
		} else {
			return false;
		}
	}
	
	protected static function loadCachedPaths() {
		if (is_null(self::$cachedPaths)) {
			if (self::$cacheFilePath && is_file(self::$cacheFilePath)) {
				self::$cachedPaths = unserialize(file_get_contents(self::$cacheFilePath));
			}
		}
	}
	
	protected static function saveCachedPaths() {
		if (!file_exists(self::$cacheFilePath) || is_writable(self::$cacheFilePath)) {
			$fileContents = serialize(self::$cachedPaths);
			$f = fopen(self::$cacheFilePath, 'w');
			if ($f === false) {
				trigger_error('Autoloader could not write the cache file: ' . self::$cacheFilePath, E_USER_ERROR);
			} else {
				fwrite($f, $fileContents);
				fclose($f);
			}
		} else {
			trigger_error('Autoload cache file not writable: ' . self::$cacheFilePath, E_USER_ERROR);
		}
	}
	
	protected static function searchForClassFile($className, $directory) {
		if (is_dir($directory) && is_readable($directory)) {
			$d = dir($directory);
			while ( ($f = $d->read()) ) {
				$subPath = $directory . $f;
				if (is_dir($subPath)) {
					// Found a subdirectory
					if (!preg_match(self::$excludeFolderNames, $f)) {
						if ( ($filePath = self::searchForClassFile($className, $subPath . '/')) ) {
							return $filePath;
						}
					}
				} else {
					// Found a file
					if ($f == $className . self::$classFileSuffix) {
						return $subPath;
					}
				}
			}
		}
		return false;
	}
	
}

?>