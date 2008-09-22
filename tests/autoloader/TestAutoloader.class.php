<?php

class TestAutoloader extends UnitTestCase
{
	protected $cacheFile = '';
	
	public function __construct()
	{
		parent::__construct();
		
		$this->cacheFile = dirname(__FILE__) . 'class_path_cache.txt';
		$this->removeCacheFile();
		
		include_once(dirname(dirname(dirname(__FILE__))) . '/extras/Autoloader.class.php');
		Autoloader::addClassPath(dirname(__FILE__) . '/classes_app/');
		Autoloader::addClassPath(dirname(__FILE__) . '/classes_shared/');
		Autoloader::setCacheFilePath($this->cacheFile);
		//Autoloader::excludeFolderNamesMatchingRegex('/^CVS|\..*$/');
		spl_autoload_register(array('Autoloader', 'loadClass'));
	}
	
	public function __destruct()
	{
		// Not sure how to put this in the testing framework since we are testing
		// functionality as the script ends...
		if (!file_exists($this->cacheFile))
		{
			trigger_error('Cache file was not saved at end of script', E_USER_ERROR);
		}
		$this->removeCacheFile();
	}
	
	public function removeCacheFile()
	{
		if (file_exists($this->cacheFile)) {
			unlink($this->cacheFile);
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
		$this->assertEqual($obj->getClassLocation(), 'app');
	}
	
	public function testExtendedClassesAreAlsoAutoloaded()
	{
		$obj = new AppSession();
		$this->assertTrue($obj instanceof AppSession);
		$this->assertTrue($obj instanceof GenericSession);
	}
	
	public function testCacheFileSavingIsDelayedUntilScriptEnds()
	{
		$this->assertFalse(file_exists($this->cacheFile), 'Cache file should not exist yet');
	}
	
	public function testCacheFileCanBeManuallySaved()
	{
		Autoloader::saveCachedPaths();
		$this->assertTrue(file_exists($this->cacheFile), 'Cache file should exist');
		$this->assertTrue(filesize($this->cacheFile) > 0, 'Cache file should be non-empty');
		$this->removeCacheFile();
	}
}

?>