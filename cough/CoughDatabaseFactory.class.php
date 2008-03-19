<?php

/**
 * A simple factory that provides access to an application's database objects.
 * It should be dynamically initialized, and can hold mixed types of database
 * adapter objects.
 * 
 * EXAMPLE INITIALIZATION:
 * 
 * CoughDatabaseFactory::addDatabase('alias1', CoughPdoDatabase::constructByConfig($config1));
 * CoughDatabaseFactory::addDatabase('alias2', CoughAsDatabase::constructByConfig($config2));
 * 
 * EXAMPLE RETRIEVAL:
 * 
 * $db = CoughDatabaseFactory::getDatabase('alias1');
 * 
 * 
 * Alternatively, you can have databases added on the fly as you request them
 * by specifying the configuration for each database:
 * 
 * Assuming we have:
 *    
 *    $configs = array(
 *        array(
 *            'aliases' => array('foo'),
 *            'adapter' => 'as',
 *            'driver' => 'mysql',
 *            'db_name' => 'new_user',
 *            'host' => 'localhost',
 *            'user' => 'nobody',
 *            'pass' => '',
 *            'port' => 3307,
 *        ),
 *        array(
 *            'aliases' => array('user'),
 *            'adapter' => 'as',
 *            'driver' => 'mysql',
 *            'db_name' => 'user',
 *            'host' => 'localhost',
 *            'user' => 'nobody',
 *            'pass' => '',
 *            'port' => 3306,
 *        )
 *    );
 *    
 * Option 1: Add one config at a time:
 *    
 *    CoughDatabaseFactory::addConfig($configs[0]);
 *    CoughDatabaseFactory::addConfig($configs[1]);
 *    
 * Option 2: Add all configs at once:
 *    
 *    CoughDatabaseFactory::setConfigs($configs);
 * 
 * 
 * @package dal
 * @author Anthony Bush, Lewis Zhang
 **/
class CoughDatabaseFactory
{
	/**
	 * Format:
	 * 
	 * [alias] => [CoughDatabaseInterface]
	 * 
	 * @var array
	 **/
	protected static $databases = array();
	
	/**
	 * An array of database config info.
	 * 
	 * When a database is retrieved, if it is not already created, then it gets
	 * created using the configuration info specified in this array, if that
	 * info exists.
	 * 
	 * Format:
	 * 
	 * array(
	 *     'adapter' => 'as',  # optional, default: as
	 *     'adapter_class_prefix' => 'CoughAs',  # optional, default: 'Cough' plus the titlecase of adapter
	 *     'adapter_class_path' => '/my/path/',  # optional, default: cough module directory plus "dal" plus adapter
	 *     'aliases' => array('another_alias'),
	 *     'driver' => 'mysql',
	 *     'host' => 'localhost',
	 *     'db_name' => 'user',
	 *     'user' => 'nobody',
	 *     'pass' => '',
	 *     'port' => 3306
	 * );
	 * 
	 * @var array
	 **/
	protected static $configs = array();
	
	/**
	 * Sets all the database configs at once.
	 *
	 * @return void
	 * @see $configs
	 * @author Anthony Bush
	 **/
	public static function setConfigs($configs)
	{
		self::$configs = array();
		foreach ($configs as $config) {
			self::addConfig($config);
		}
	}
	
	/**
	 * Sets the database config for the specified database alias.
	 *
	 * @return void
	 * @see $configs
	 * @author Anthony Bush
	 **/
	public static function addConfig($config)
	{
		self::$configs[] = $config;
	}
	
	public static function addDatabase($alias, $dbObject)
	{
		self::$databases[$alias] = $dbObject;
	}
	
	public static function getDatabase($alias)
	{
		if (isset(self::$databases[$alias]))
		{
			// We already have the database object in memory
			return self::$databases[$alias];
		}
		else
		{
			// Loop through all config arrays looking for one that is setup for the specified alias
			foreach (self::$configs as $config)
			{
				if (in_array($alias, $config['aliases']))
				{
					$dbObject = self::constructDatabaseByConfig($config);
					foreach ($config['aliases'] as $configAlias)
					{
						self::addDatabase($configAlias, $dbObject);
					}
					return $dbObject;
				}
			}
		}
		
		return null;
	}
	
	/**
	 * returns the correct database adapter object
	 *
	 * @return CoughDatabaseInterface concrete class implementing CoughDatabaseInterface
	 * @see $configs
	 * @author Lewis Zhang, Anthony Bush
	 **/
	public static function constructDatabaseByConfig($dbConfig)
	{
		if (isset($dbConfig['adapter'])) {
			$adapter = $dbConfig['adapter'];
		} else {
			$adapter = 'as';
		}
		
		if (isset($dbConfig['adapter_class_prefix'])) {
			$classPrefix = $dbConfig['adapter_class_prefix'];
		} else {
			$classPrefix = 'Cough' . str_replace(' ', '', ucwords(str_replace('_', ' ', $adapter)));
		}
		
		if (isset($dbConfig['adapter_class_path'])) {
			$classPath = $dbConfig['adapter_class_path'];
		} else {
			$classPath = dirname(dirname(__FILE__)) . '/dal/' . $adapter . '/';
		}
		
		$adapterDatabaseClassName = $classPrefix . 'Database';
		$adapterDatabaseResultClassName = $classPrefix . 'DatabaseResult';
		
		require_once($classPath . $adapterDatabaseClassName . '.class.php');
		require_once($classPath . $adapterDatabaseResultClassName . '.class.php');
		
		return call_user_func(array($adapterDatabaseClassName, 'constructByConfig'), $dbConfig);
	}
	
	/**
	 * Get all the database configs CoughDatabaseFactory is currently aware of.
	 * Could be useful for debugging purposes.
	 * 
	 * @return array
	 * @see $configs
	 **/
	public static function getConfigs()
	{
		return self::$configs;
	}
	
	/**
	 * Get all the currently constructed database objects.
	 * Could be useful for debugging purposes.
	 * 
	 * @return array
	 * @see $databases
	 **/
	public static function getDatabases()
	{
		return self::$databases;
	}
	
	/**
	 * Restore CoughDatabaseFactory to its initial state (no configs, no database
	 * objects).
	 *
	 * @return void
	 **/
	public static function reset()
	{
		self::$databases = array();
		self::$configs = array();
	}
}

?>
