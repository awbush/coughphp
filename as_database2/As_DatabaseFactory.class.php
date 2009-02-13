<?php

/**
 * THIS FILE IS NOT USED BY COUGH -- CoughDatabaseFactory is used instead.  This
 * is simply part of the original "as_database" module.
 * 
 * A simple factory that provides access to an application's database objects.
 * It should be dynamically initialized, and can hold mixed types of database
 * objects (e.g. PEAR::DB, AS Database, DatabaseConnector, Persistent).
 * 
 * EXAMPLE INITIALIZATION:
 * 
 * As_DatabaseFactory::addDatabase('content', new As_Database('content'));
 * As_DatabaseFactory::addDatabase('user', new As_Database('user'));
 * As_DatabaseFactory::addDatabase('new_user', new As_Database('new_user'));
 * 
 * EXAMPLE RETRIEVAL:
 * 
 * $db = As_DatabaseFactory::getDatabase('content');
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
 *    As_DatabaseFactory::addDatabaseConfig('foo', $dbConfigs['foo']);
 *    As_DatabaseFactory::addDatabaseConfig('user', $dbConfigs['user']);
 *    
 * Option 2: Add all configs at once:
 *    
 *    As_DatabaseFactory::setDatabaseConfigs($dbConfigs);
 * 
 * @package as_database
 * @author Anthony Bush
 **/
class As_DatabaseFactory {
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
	 * [dbObjName] => array(
	 *     'db_name' => 'user',
	 *     'host' => 'localhost',
	 *     'user' => 'nobody',
	 *     'pass' => '',
	 *     'port' => 3306
	 * );
	 * 
	 * @var array
	 **/
	protected static $dbConfigs = array(); // [dbObjName] => [config array]
	
	/**
	 * Sets all the database configs at once.
	 *
	 * @return void
	 * @see $dbConfigs
	 * @author Anthony Bush
	 **/
	public static function setDatabaseConfigs($dbConfigs) {
		self::$dbConfigs = $dbConfigs;
	}
	
	/**
	 * Sets the database config for the specified database object name.
	 *
	 * @return void
	 * @see $dbConfigs
	 * @author Anthony Bush
	 **/
	public static function addDatabaseConfig($dbObjName, $dbConfig) {
		self::$dbConfigs[$dbObjName] = $dbConfig;
	}
	
	public static function addDatabase($dbName, $dbObject) {
		self::$databases[$dbName] = $dbObject;
	}
	
	public static function getDatabase($dbAliasName, $dbName = null) {
		if (isset(self::$databases[$dbAliasName])) {
			
			// We already have the database object in memory
			$dbObject = self::$databases[$dbAliasName];
			if (!is_null($dbName)) {
				$dbObject->selectDB($dbName);
			}
			return $dbObject;
			
		} else {
			
			// The database object is not already in memory, attempt to add it.
			if (isset(self::$dbConfigs[$dbAliasName])) {
				
				// Use the config to construct and add the database:
				$config =& self::$dbConfigs[$dbAliasName];
				// The port option isn't currectly required to be set
				if (isset($config['port'])) {
					self::addDatabase($dbAliasName, new As_Database($config['db_name'], $config['host'], $config['user'], $config['pass'], $config['port']));
				} else {
					self::addDatabase($dbAliasName, new As_Database($config['db_name'], $config['host'], $config['user'], $config['pass']));
				}
				
			} else {
				
				// No configuration information... we should throw error here instead of relaying on As_Database class default host/user/pass values.
				if (is_null($dbName)) {
					$newDbName = $dbAliasName;
				} else {
					$newDbName = $dbName;
				}
				// No config? Try creating using generate host/user/pass
				self::addDatabase($dbAliasName, new As_Database($newDbName));
				
			}
			
			// We have the database object in memory now.
			$dbObject = self::$databases[$dbAliasName];
			if (!is_null($dbName)) {
				$dbObject->selectDB($dbName);
			}
			return $dbObject;
		}
	}
}

?>