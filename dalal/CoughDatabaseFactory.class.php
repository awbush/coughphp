<?php

/**
 * A simple factory that provides access to an application's database objects.
 * It should be dynamically initialized, and can hold mixed types of database
 * objects (e.g. PEAR::DB, AS Database, DatabaseConnector, Persistent).
 * 
 * EXAMPLE INITIALIZATION:
 * 
 * CoughDatabaseFactory::addDatabase('content', new As_Database('content'));
 * CoughDatabaseFactory::addDatabase('user', new As_Database('user'));
 * CoughDatabaseFactory::addDatabase('new_user', new As_Database('new_user'));
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
 *            'db_name' => 'new_user',
 *            'host' => 'localhost',
 *            'user' => 'nobody',
 *            'pass' => '',
 *            'port' => 3307,
 *        ),
 *        'user' => array(
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
	 *     'driver' => 'mysqli',
	 *     'host' => 'localhost',
	 *     'db_name' => 'user',
	 *     'user' => 'nobody',
	 *     'pass' => '',
	 *     'port' => 3306
	 * );
	 * 
	 * @var array
	 **/
	protected static $dbConfigs = array(); // [dbAlias] => [config array]
	
	/**
	 * the adapter to use (otherwise known as dalal)
	 * 
	 * This can be set with CoughDatabaseFactory::setAdapter('adapter_name').
	 *
	 * @var string
	 **/
	protected static $adapterName = null;
	
	/**
	 * the default database configuration
	 * 
	 * This holds common driver and port values, and gets array_merge'd with the user database config.
	 *
	 * @var array
	 **/
	protected static $defaultDbConfig = array(
		'driver' => 'mysql',
		'host' => 'localhost',
		'db_name' => null,
		'user' => null,
		'pass' => null,
		'port' => '3306'
	);
	
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
			self::$dbConfigs[$dbAlias] = array_merge(self::$defaultDbConfig, $dbConfig);
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
		self::$dbConfigs[$dbAlias] = array_merge(self::$defaultDbConfig, $dbConfig);
	}
	
	/**
	 * specifies which database adapter to use (e.g. as, pdo, creole)
	 * 
	 * This defaults to 'as', the adapter that comes prepackaged with Cough.
	 * WARNING: This only sets the adapter once; subsequent calls will do nothing
	 *
	 * @return bool - whether or not the adapter was set
	 * @author Lewis Zhang
	 **/
	public static function setAdapter($adapterName)
	{
		if (is_null(self::$adapterName)) {
			self::$adapterName = $adapterName;
			return true;
		}
		return false;
	}
	
	public static function addDatabase($dbAlias, $dbObject) {
		self::$databases[$dbAlias] = $dbObject;
	}
	
	public static function getDatabase($dbAlias)
	{
		// we have to make sure the the adapter name is set (at least to default 'as') before doing anything
		self::setAdapter('as');
		
		if (isset(self::$databases[$dbAlias])) {
			// We already have the database object in memory
			$dbObject = self::$databases[$dbAlias];
			
			return $dbObject;
		}
		else {
			// The database object is not already in memory, attempt to add it.
			if (isset(self::$dbConfigs[$dbAlias])) {
				// Use the config to construct and add the database:
				$config =& self::$dbConfigs[$dbAlias];
				self::addDatabase($dbAlias, self::retrieveAdapterByDbConfig($config));
			}
			else {
				// No configuration information... we should throw error here instead of relaying on As_Database class default host/user/pass values.
				// if (is_null($dbName)) {
				// 	$newDbName = $dbAlias;
				// }
				// else {
				// 	$newDbName = $dbName;
				// }
				// // No config? Try creating using generate host/user/pass
				// self::addDatabase($dbAlias, new As_Database($newDbName));
				
				// lzhang: I've decided to return null for now
				return null;
			}
			
			// We have the database object in memory now.
			$dbObject = self::$databases[$dbAlias];
			
			return $dbObject;
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
		switch (self::$adapterName) {
			case 'pdo':
				return CoughPdoDatabaseAdapter::retrieveByDbConfig($dbConfig);
				break;
			
			case 'as':
			default:
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
	
	public static function getAdapter()
	{
		return self::$adapterName;
	}
	
	public static function getDatabases()
	{
		return self::$databases;
	}
	
	public static function reset()
	{
		self::$databases = array();
		self::$dbConfigs = array();
		self::$adapterName = null;
	}
}

?>