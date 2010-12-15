<?php
class AutoloaderTest extends PHPUnit_Framework_TestCase
{
	protected static $cacheFile = '';
	
	public static function setUpBeforeClass()
	{
		self::$cacheFile = dirname(__FILE__) . '/class_path_cache.txt';
		self::removeCacheFile();
		
		include_once(dirname(dirname(dirname(__FILE__))) . '/extras/Autoloader.class.php');
		Autoloader::addClassPath(dirname(__FILE__) . '/classes_app/');
		Autoloader::addClassPath(dirname(__FILE__) . '/classes_shared/');
		Autoloader::setCacheFilePath(self::$cacheFile);
		//Autoloader::excludeFolderNamesMatchingRegex('/^CVS|\..*$/');
		spl_autoload_register(array('Autoloader', 'loadClass'));
	}
	
	public static function removeCacheFile()
	{
		if (file_exists(self::$cacheFile)) {
			unlink(self::$cacheFile);
		}
	}
	
	public function testClassesCanBeAutoloaded()
	{
		$obj = new CoolAppClass();
		$this->assertTrue($obj instanceof CoolAppClass);
	}
	
	public function testAppCanOverrideClasses()
	{
		$obj = new As_String();
		$this->assertEquals($obj->getClassLocation(), 'app');
	}
	
	public function testExtendedClassesAreAlsoAutoloaded()
	{
		$obj = new AppSession();
		$this->assertTrue($obj instanceof AppSession);
		$this->assertTrue($obj instanceof GenericSession);
	}
	
	public function testCacheFileSavingIsDelayedUntilScriptEnds()
	{
		$this->assertFalse(file_exists(self::$cacheFile), 'Cache file should not exist yet');
	}
	
	public function testCacheFileCanBeManuallySaved()
	{
		Autoloader::saveCachedPaths();
		$this->assertTrue(file_exists(self::$cacheFile), 'Cache file should exist');
		$this->assertTrue(filesize(self::$cacheFile) > 0, 'Cache file should be non-empty');
		$this->removeCacheFile();
	}
}

?>