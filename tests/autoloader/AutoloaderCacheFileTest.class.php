<?php
class AutoloaderCacheFileTest extends PHPUnit_Framework_TestCase
{
	protected static $cacheFile = '';
	
	public static function setUpBeforeClass()
	{
		self::$cacheFile = dirname(__FILE__) . '/class_path_cache.txt';
	}
	
	public static function removeCacheFile()
	{
		if (file_exists(self::$cacheFile)) {
			unlink(self::$cacheFile);
		}
	}
	
	public function testWasCacheFileCreated()
	{
		$this->assertFileExists(self::$cacheFile);
		self::removeCacheFile();
	}
}
