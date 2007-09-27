<?php

/**
 * manages a singleton instance of the database connection
 *
 * @package default
 * @author Lewis Zhang
 **/
class CoughDatabaseFactory
{
	/**
	 * the database abstraction layer abstraction layer object
	 *
	 * @var CoughAbstractDatabaseAdapter
	 **/
	protected static $db = null;
	
	/**
	 * returns an instance of the database connection if it exists; otherwise it creates a new database connection and returns it
	 *
	 * @return CoughAbstractDatabaseAdapter
	 * @author Lewis Zhang
	 **/
	public static function getDb($dsn)
	{
		if (is_null(self::$db)) {
			self::$db = CoughAbstractDatabaseAdapter::retrieveByDsn($dsn);
		}
		return self::$db;
	}
} // END class CoughDatabaseFactory

?>