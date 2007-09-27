<?php

/**
 * manages a singleton instance of the database connection
 *
 * @package default
 * @author Lewis Zhang
 **/
class Lz_DatabaseFactory
{
	/**
	 * the database abstraction layer abstraction layer object
	 *
	 * @var Lz_Database
	 **/
	protected static $db = null;
	
	/**
	 * returns an instance of the database connection if it exists; otherwise it creates a new database connection and returns it
	 *
	 * @return Lz_Database
	 * @author Lewis Zhang
	 **/
	public static function getDb($dsn)
	{
		if (is_null(self::$db)) {
			self::$db = Lz_Database::retrieveByDsn($dsn);
		}
		return self::$db;
	}
} // END class Lz_DatabaseFactory

?>