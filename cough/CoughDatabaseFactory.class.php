<?php

/**
 * A simple factory that provides access to an application's database objects.
 * It should be dynamically initialized, and can hold mixed types of database
 * adapter objects.
 * 
 * EXAMPLE RETRIEVAL:
 * 
 *     <code>
 *     $db = CoughDatabaseFactory::getDatabase('alias1');
 *     $dbName = CoughDatabaseFactory::getDatabaseName('alias1');
 *     </code>
 * 
 * EXAMPLE INITIALIZATION:
 * 
 * Assuming we have:
 *    
 *     <code>
 *     $configs = array(
 *         array(
 *             'db_name_hash' => array('alias1' => 'actual_db_name'),
 *             'adapter' => 'as',
 *             'driver' => 'mysql',
 *             'host' => 'localhost',
 *             'user' => 'nobody',
 *             'pass' => '',
 *             'port' => 3307,
 *         ),
 *         array(
 *             'db_name_hash' => array('my_app' => 'my_app'),
 *             'adapter' => 'as',
 *             'driver' => 'mysql',
 *             'host' => 'localhost',
 *             'user' => 'nobody',
 *             'pass' => '',
 *             'port' => 3306,
 *         )
 *     );
 *     </code>
 *    
 * Option 1: Add one config at a time:
 *    
 *     <code>
 *     CoughDatabaseFactory::addConfig($configs[0]);
 *     CoughDatabaseFactory::addConfig($configs[1]);
 *     </code>
 *    
 * Option 2: Add all configs at once:
 *    
 *     <code>
 *     CoughDatabaseFactory::setConfigs($configs);
 *     </code>
 * 
 * The config array supports other parameters as well, but you shouldn't have to
 * use them.  See {@link $configs} for all available options.
 * 
 * @package cough
 * @author Anthony Bush, Lewis Zhang
 * @see $configs
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
	 * Format:
	 * 
	 * [alias] => [actual_db_name]
	 *
	 * @var array
	 **/
	protected static $databaseNames = array();
	
	/**
	 * An array of database config info.
	 * 
	 * When a database is retrieved, if it is not already created, then it gets
	 * created using the configuration info specified in this array, if that
	 * info exists.
	 * 
	 * Format:
	 * 
	 *     <code>
	 *     array(
	 *         # Which database adapter (DAL) to use. optional, default: as
	 *         'adapter' => 'as',
	 * 
	 *         # Adapter class name prefix. optional, default: 'Cough' plus the titlecase of adapter
	 *         'adapter_class_prefix' => 'CoughAs',
	 * 
	 *         # Adapter location. optional, default: cough module directory plus "dal" plus adapter
	 *         'adapter_class_path' => '/my/path/',
	 * 
	 *         # Hash of aliases to actual database names.  Most likely the alias
	 *         # will be the name of the database that generation took place on and the
	 *         # actual database name will be the same.  If a different environment
	 *         # (e.g. production/test/dev) uses a different database name, then just
	 *         # change the actual database name to that database in that environment's
	 *         # config file, leaving the alias/key part of the hash alone.
	 *         # required if old "aliases" param not given.
	 *         'db_name_hash' => array(
	 *             'alias1' => 'actual_db_name1',
	 *             'alias2' => 'actual_db_name2'
	 *         ),
	 * 
	 *         # old way of specifying connection aliases did not include db name
	 *         # remapping ability. Use "db_name_hash" instead.
	 *         'aliases' => array('actual_db_name1', 'actual_db_name2'),
	 * 
	 *         # the rest of these should be obvious
	 *         'driver' => 'mysql',
	 *         'host' => 'localhost',
	 *         'user' => 'nobody',
	 *         'pass' => '',
	 *         'port' => 3306
	 *     );
	 *     </code>
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
	 * Adds the database config for later use.  Make sure to specified the 'aliases'
	 * or the 'db_name_hash' value.
	 *
	 * @return void
	 * @see $configs
	 * @author Anthony Bush
	 **/
	public static function addConfig($config)
	{
		// Add database name mappings to the config if not already there, and add to the
		// global hash as well.
		if (isset($config['aliases']))
		{
			// build from old-style "aliases" parameter (with added mapping ability)
			$dbNameHash = array();
			foreach ($config['aliases'] as $alias => $dbName)
			{
				if (is_int($alias))
				{
					$dbNameHash[$dbName] = $dbName;
				}
				else
				{
					$dbNameHash[$alias] = $dbName;
				}
			}
			$config['db_name_hash'] = $dbNameHash;
		}
		else if (!isset($config['db_name_hash']))
		{
			throw new Exception('Must specify the "aliases" or the "db_name_hash" parameter in the config.');
		}
		
		self::$databaseNames = $config['db_name_hash'] + self::$databaseNames;
		self::$configs[] = $config;
	}
	
	/**
	 * Adds the database object for the specified alias name.
	 * 
	 * It's better to add configs b/c then a database object/connection won't be made
	 * unless one is needed.
	 *
	 * @return void
	 * @see addConfig(), setConfigs()
	 * @author Anthony Bush
	 **/
	public static function addDatabase($alias, $dbObject)
	{
		self::$databases[$alias] = $dbObject;
	}
	
	/**
	 * Get the database object for the specified alias
	 * 
	 * @param string $alias
	 * @param string $dbName optional database name to select before returning the database object.
	 * @return CoughDatabaseInterface|null
	 **/
	public static function getDatabase($alias, $dbName = null)
	{
		if (isset(self::$databases[$alias]))
		{
			// We already have the database object in memory
			$dbObject = self::$databases[$alias];
			if (!empty($dbName))
			{
				$dbObject->selectDb($dbName);
			}
			return $dbObject;
		}
		else
		{
			// Loop through all config arrays looking for one that is setup for the specified alias
			foreach (self::$configs as $config)
			{
				if (isset($config['db_name_hash'][$alias]))
				{
					$dbObject = self::constructDatabaseByConfig($config);
					foreach ($config['db_name_hash'] as $configAlias => $actualDbName)
					{
						self::addDatabase($configAlias, $dbObject);
					}
					if (!empty($dbName))
					{
						$dbObject->selectDb($dbName);
					}
					return $dbObject;
				}
			}
		}
		
		// As of CoughPHP 1.3 we now throw verbose exception.
		throw new Exception('The alias "' . $alias . '" does not exist. Make sure your config calls CoughDatabaseFactory::addConfig() or CoughDatabaseFactory::setConfigs().');
		// return null;
	}
	
	/**
	 * Get the actual database name for the specified alias.
	 * 
	 * If no mapping exists, it returns the original alias value.
	 *
	 * @return string
	 **/
	public static function getDatabaseName($alias)
	{
		if (isset(self::$databaseNames[$alias]))
		{
			return self::$databaseNames[$alias];
		}
		return $alias;
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
		self::$databaseNames = array();
		self::$configs = array();
	}
	
	/**
	 * Same as {@link getDatabases()}, except it rolls up all aliases using the same
	 * connection into one array entry.
	 *
	 * @return array of objects that implement CoughDatabaseInterface
	 * @author Anthony Bush
	 * @since 2008-09-09
	 **/
	public static function getUniqueDatabases()
	{
		$uniqueDbs = array();
		foreach (self::$configs as $config)
		{
			foreach ($config['db_name_hash'] as $alias => $actualDbName)
			{
				if (isset(self::$databases[$alias]))
				{
					$uniqueDbs[implode(', ', array_keys($config['db_name_hash']))] = self::$databases[$alias];
				}
			}
		}
		return $uniqueDbs;
	}
}

?>
