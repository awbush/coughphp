<?php

/**
 * A simple factory that provides access to an application's database objects.
 * It should be dynamically initialized, and can hold mixed types of database
 * adapter objects.
 * 
 * EXAMPLE INITIALIZATION:
 * 
 * CoughDatabaseFactory::addDatabase('content', CoughPdoDatabaseAdapter::retrieveByDbConfig($config1));
 * CoughDatabaseFactory::addDatabase('user', CoughAsDatabaseAdapter::retrieveByDbConfig($config2));
 * 
 * EXAMPLE RETRIEVAL:
 * 
 * $db = CoughDatabaseFactory::getDatabase('content');
 * 
 * 
 * Alternatively, you can have databases added on the fly as you request them
 * by specifying the configuration for each database:
 * 
 * Assuming we have:
 *    
 *    $dbConfigs = array(
 *        'foo' => array(
 *            'adapter' => 'as',
 *            'driver' => 'mysql',
 *            'db_name' => 'new_user',
 *            'host' => 'localhost',
 *            'user' => 'nobody',
 *            'pass' => '',
 *            'port' => 3307,
 *        ),
 *        'user' => array(
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
 *    CoughDatabaseFactory::addDatabaseConfig('foo', $dbConfigs['foo']);
 *    CoughDatabaseFactory::addDatabaseConfig('user', $dbConfigs['user']);
 *    
 * Option 2: Add all configs at once:
 *    
 *    CoughDatabaseFactory::setDatabaseConfigs($dbConfigs);
 * 
 * 
 * @author Anthony Bush
 **/
class CoughDatabaseFactory
{
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
	 * [dbAlias] => array(
	 *     'adapter' => 'as',
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
	protected static $dbConfigs = array();
	
	/**
	 * Sets all the database configs at once.
	 *
	 * @return void
	 * @see $dbConfigs
	 * @author Anthony Bush
	 **/
	public static function setDatabaseConfigs($dbConfigs)
	{
		self::$dbConfigs = array();
		foreach ($dbConfigs as $dbAlias => $dbConfig) {
			self::$dbConfigs[$dbAlias] = $dbConfig;
		}
	}
	
	/**
	 * Sets the database config for the specified database alias.
	 *
	 * @return void
	 * @see $dbConfigs
	 * @author Anthony Bush
	 **/
	public static function addDatabaseConfig($dbAlias, $dbConfig)
	{
		self::$dbConfigs[$dbAlias] = $dbConfig;
	}
	
	public static function addDatabase($dbAlias, $dbObject) {
		self::$databases[$dbAlias] = $dbObject;
	}
	
	public static function getDatabase($dbAlias)
	{
		if (isset(self::$databases[$dbAlias]))
		{
			// We already have the database object in memory
			return self::$databases[$dbAlias];
		}
		else
		{
			// The database object is not already in memory, attempt to add it.
			if (isset(self::$dbConfigs[$dbAlias]))
			{
				// Use the config to construct and add the database:
				$dbObject = self::retrieveAdapterByDbConfig(self::$dbConfigs[$dbAlias]);
				self::addDatabase($dbAlias, $dbObject);
				return $dbObject;
			}
			else
			{
				return null;
			}
		}
	}
	
	/**
	 * returns the correct database adapter object
	 *
	 * @return object - a concrete child class of CoughAbstractDatabaseAdapter
	 * @author Lewis Zhang
	 **/
	protected static function retrieveAdapterByDbConfig($dbConfig)
	{
		if (isset($dbConfig['adapter'])) {
			$adapter = $dbConfig['adapter'];
		} else {
			$adapter = 'as';
		}
		
		// Make sure the base classes are loaded
		$driverPath = dirname(__FILE__) . '/drivers/';
		require_once($driverPath . 'base/CoughAbstractDatabaseAdapter.class.php');
		require_once($driverPath . 'base/CoughAbstractDatabaseResultAdapter.class.php');
		
		// Load the concrete adapter classes and return a database adapter object.
		switch ($adapter) {
			case 'pdo':
				require_once($driverPath . 'pdo/CoughPdoDatabaseAdapter.class.php');
				require_once($driverPath . 'pdo/CoughPdoDatabaseResultAdapter.class.php');
				return CoughPdoDatabaseAdapter::retrieveByDbConfig($dbConfig);
				break;
			
			case 'as':
			default:
				require_once($driverPath . 'as/CoughAsDatabaseAdapter.class.php');
				require_once($driverPath . 'as/CoughAsDatabaseResultAdapter.class.php');
				return CoughAsDatabaseAdapter::retrieveByDbConfig($dbConfig);
				break;
		}
	}
	
	/**
	 * Unit Test Methods
	 *
	 * The methods below are for unit testing purposes only!
	 */
	
	public static function getDatabaseConfigs()
	{
		return self::$dbConfigs;
	}
	
	public static function getDatabases()
	{
		return self::$databases;
	}
	
	public static function reset()
	{
		self::$databases = array();
		self::$dbConfigs = array();
	}
}

?>